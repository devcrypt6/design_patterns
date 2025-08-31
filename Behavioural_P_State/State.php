<?php
declare(strict_types=1);

/**
 * Lets consider an order processing system where an order can be in one of several states:
 * New, Paid, Shipped, Delivered, Cancelled.
 */
namespace App\State\Order;

interface OrderState {
    public function pay(Order $o): void;
    public function ship(Order $o): void;
    public function deliver(Order $o): void;
    public function cancel(Order $o): void;
}

final class Order {
    private OrderState $state;
    public function __construct(public string $id) { $this->state = new NewState(); }
    public function setState(OrderState $s): void { $this->state = $s; }

    public function pay(): void     { $this->state->pay($this); }
    public function ship(): void    { $this->state->ship($this); }
    public function deliver(): void { $this->state->deliver($this); }
    public function cancel(): void  { $this->state->cancel($this); }
}

/** States  */
final class NewState implements OrderState {
    public function pay(Order $o): void     { echo " paid\n"; $o->setState(new PaidState()); }
    public function ship(Order $o): void    { self::err('must pay first'); }
    public function deliver(Order $o): void { self::err('must pay & ship first'); }
    public function cancel(Order $o): void  { echo " cancelled\n"; $o->setState(new CancelledState()); }
    private static function err(string $m): void { echo " $m\n"; }
}

final class PaidState implements OrderState {
    public function pay(Order $o): void     { echo "already paid\n"; }
    public function ship(Order $o): void    { echo "shipped\n"; $o->setState(new ShippedState()); }
    public function deliver(Order $o): void { echo "cannot deliver before shipping\n"; }
    public function cancel(Order $o): void  { echo "efund + cancel\n"; $o->setState(new CancelledState()); }
}

final class ShippedState implements OrderState {
    public function pay(Order $o): void     { echo "already paid\n"; }
    public function ship(Order $o): void    { echo "already shipped\n"; }
    public function deliver(Order $o): void { echo "delivered\n"; $o->setState(new DeliveredState()); }
    public function cancel(Order $o): void  { echo "cannot cancel after shipping\n"; }
}

final class DeliveredState implements OrderState {
    public function pay(Order $o): void     { echo "order closed\n"; }
    public function ship(Order $o): void    { echo "order closed\n"; }
    public function deliver(Order $o): void { echo "olready delivered\n"; }
    public function cancel(Order $o): void  { echo "cannot cancel delivered order\n"; }
}

final class CancelledState implements OrderState {
    public function pay(Order $o): void     { echo "cancelled; cannot pay\n"; }
    public function ship(Order $o): void    { echo "cancelled; cannot ship\n"; }
    public function deliver(Order $o): void { echo "cancelled; cannot deliver\n"; }
    public function cancel(Order $o): void  { echo "already cancelled\n"; }
}

/**
 * So, on each state is represented by a separate class that implements the OrderState interface.
 * 
 * When an action is performed on the order, it also update the state of the order accordingly.
 */
$order = new Order('ORD-1');
$order->ship();     // must pay first
$order->pay();      // paid
$order->deliver();  // cannot deliver before shipping
$order->ship();     // shipped
$order->deliver();  // delivered
