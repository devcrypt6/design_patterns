<?php

namespace App\CartCommand;

/**
 * Let say you are working on Shopping Cart, you will have multiple feature inside, AddItem, Remove, Undo, Redo.
 * 
 * Let's define step by step
 */


/**
 * Command interface
 */
interface Command {
    public function execute(): void;
    public function undo(): void;
    public function name(): string;
}

/**
 * Client: cart function
 */
final class Cart
{
    private array $items = [];
    private ?int $couponPercent = null; // 0..100

    public function addItem(string $sku, int $qty, int $priceCents): void
    {
        if ($qty <= 0 || $priceCents <= 0) {
            throw new \InvalidArgumentException('qty and price must be > 0');
        }
        if (!isset($this->items[$sku])) {
            $this->items[$sku] = ['qty' => $qty, 'priceCents' => $priceCents];
        } else {
            // Simple rule: keep first price, just add qty
            $this->items[$sku]['qty'] += $qty;
        }
    }

    public function removeItem(string $sku, int $qty): void
    {
        if (!isset($this->items[$sku])) {
            throw new \RuntimeException("SKU $sku not in cart");
        }
        if ($qty <= 0) {
            throw new \InvalidArgumentException('qty must be > 0');
        }
        $this->items[$sku]['qty'] -= $qty;
        if ($this->items[$sku]['qty'] <= 0) {
            unset($this->items[$sku]);
        }
    }

    public function applyCoupon(int $percent): void
    {
        if ($percent < 0 || $percent > 100) {
            throw new \InvalidArgumentException('coupon must be 0..100');
        }
        $this->couponPercent = $percent;
    }

    public function totalCents(): int
    {
        $sum = 0;
        foreach ($this->items as $row) {
            $sum += $row['qty'] * $row['priceCents'];
        }
        if ($this->couponPercent !== null) {
            // round to nearest cent without floats
            $sum = intdiv($sum * (100 - $this->couponPercent) + 50, 100);
        }
        return $sum;
    }

    /** For debugging/demo */
    public function describe(): string
    {
        $lines = [];
        foreach ($this->items as $sku => $row) {
            $lines[] = sprintf("- %s ×%d @ %s", $sku, $row['qty'], self::money($row['priceCents']));
        }
        $lines[] = "Coupon: " . ($this->couponPercent === null ? "—" : $this->couponPercent."%");
        $lines[] = "Total:  " . self::money($this->totalCents());
        return implode("\n", $lines);
    }

    private static function money(int $cents): string
    {
        return "€".number_format($cents/100, 2, ',', '.');
    }

    public function snapshot(): CartSnapshot
    {
        return new CartSnapshot($this->items, $this->couponPercent);
    }

    public function restore(CartSnapshot $snap): void
    {
        $this->items = $snap->items;
        $this->couponPercent = $snap->couponPercent;
    }
}

final class CartSnapshot
{
    public function __construct(
        public array $items,
        public ?int $couponPercent
    ) {}
}


/**
 * Concrete Commands (they capture a snapshot before executing)
 */
abstract class SnapshottingCommand implements Command
{
    protected ?CartSnapshot $before = null;
    public function __construct(protected Cart $cart) {}

    public function undo(): void
    {
        if ($this->before) {
            $this->cart->restore($this->before);
        }
    }
}

final class AddItemCommand extends SnapshottingCommand
{
    public function __construct(Cart $cart, private string $sku, private int $qty, private int $priceCents)
    { parent::__construct($cart); }

    public function execute(): void
    {
        $this->before = $this->cart->snapshot();
        $this->cart->addItem($this->sku, $this->qty, $this->priceCents);
    }
    public function name(): string { return "AddItem({$this->sku} ×{$this->qty})"; }
}

final class RemoveItemCommand extends SnapshottingCommand
{
    public function __construct(Cart $cart, private string $sku, private int $qty)
    { parent::__construct($cart); }

    public function execute(): void
    {
        $this->before = $this->cart->snapshot();
        $this->cart->removeItem($this->sku, $this->qty);
    }
    public function name(): string { return "RemoveItem({$this->sku} ×{$this->qty})"; }
}

final class ApplyCouponCommand extends SnapshottingCommand
{
    public function __construct(Cart $cart, private int $percent)
    { parent::__construct($cart); }

    public function execute(): void
    {
        $this->before = $this->cart->snapshot();
        $this->cart->applyCoupon($this->percent);
    }
    public function name(): string { return "ApplyCoupon({$this->percent}%)"; }
}



/**
 * Invoker with undo/redo
 */
final class CartActionManager
{
    /** @var Command[] */
    private array $undoStack = [];
    /** @var Command[] */
    private array $redoStack = [];

    public function perform(Command $cmd): void
    {
        $cmd->execute();
        $this->undoStack[] = $cmd;
        $this->redoStack = []; // clear redo on new branch
        echo "[Do] {$cmd->name()}\n";
    }

    public function undo(): void
    {
        $cmd = array_pop($this->undoStack);
        if (!$cmd) return;
        $cmd->undo();
        $this->redoStack[] = $cmd;
        echo "[Undo] {$cmd->name()}\n";
    }

    public function redo(): void
    {
        $cmd = array_pop($this->redoStack);
        if (!$cmd) return;
        $cmd->execute();
        $this->undoStack[] = $cmd;
        echo "[Redo] {$cmd->name()}\n";
    }
}

/* ---------- Demo ---------- */
$cart = new Cart();
$mgr  = new CartActionManager();

$mgr->perform(new AddItemCommand($cart, 'BK-001', 2, 1299)); // 2× €12,99
$mgr->perform(new AddItemCommand($cart, 'USB-16G', 1, 799)); // 1× €7,99
$mgr->perform(new ApplyCouponCommand($cart, 10));            // 10% off
echo $cart->describe()."\n\n";

$mgr->undo();  // undo coupon
echo $cart->describe()."\n\n";

$mgr->perform(new RemoveItemCommand($cart, 'BK-001', 1)); // remove 1 book
echo $cart->describe()."\n\n";

$mgr->redo();  // (no effect; redo stack cleared by new perform)
$mgr->undo();  // undo remove book
echo $cart->describe()."\n";
