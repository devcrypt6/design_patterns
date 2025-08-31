<?php
declare(strict_types=1);

/**
 * Let's you have a E-commerce site, and on Checkout you have multiple sub-system of components.
 * 
 * It's not great idea to call components seperatly to make it happend
 * 
 * We will club them into one single access point. In our case: CheckoutFacade.php
 * 
 * Facade include these services, which combine them into to simple object.
 */

namespace App\Checkout;

/**
 * Define Services
 */
final class InventoryService {
    public function reserve(string $sku, int $qty): string {
        // Returns reservation id
        echo "[INVENTORY] reserved $qty of $sku\n";
        return 'resv_123';
    }
    public function release(string $reservationId): void {
        echo "[INVENTORY] released $reservationId\n";
    }
}

final class PaymentGateway {
    public function chargeCents(int $amountCents, string $currency, string $token): string {
        echo "[PAYMENT] charged {$amountCents}{$currency}\n";
        return 'ch_abc';
    }
    public function refund(string $chargeId, int $amountCents): void {
        echo "[PAYMENT] refunded {$amountCents}\n";
    }
}

final class InvoiceService {
    public function create(array $orderData, string $chargeId): string {
        echo "[INVOICE] created for order {$orderData['orderId']}\n";
        return 'inv_789';
    }
}

final class ShippingService {
    public function createShipment(string $orderId): string {
        echo "[SHIP] created for $orderId\n";
        return 'shp_555';
    }
}

final class AuditLogger {
    public function log(string $event, array $ctx = []): void {
        echo "[AUDIT] $event ".json_encode($ctx)."\n";
    }
}

final class CheckoutException extends \RuntimeException {}


/**
 * Define Facade to simplify access point
 */
final class CheckoutFacade
{
    public function __construct(
        private InventoryService $inventory,
        private PaymentGateway $payments,
        private InvoiceService $invoices,
        private ShippingService $shipping,
        private AuditLogger $audit
    ) {}

    /**
     * @param array{orderId:string, sku:string, qty:int, amountCents:int, currency:string, paymentToken:string} $order
     * @return array{chargeId:string, invoiceId:string, shipmentId:string}
     * @throws CheckoutException
     */
    public function placeOrder(array $order): array
    {
        $reservationId = null;
        $chargeId = null;

        try {
            $this->audit->log('checkout.started', ['orderId' => $order['orderId']]);

            // 1) Reserve inventory
            $reservationId = $this->inventory->reserve($order['sku'], $order['qty']);

            // 2) Charge payment
            $chargeId = $this->payments->chargeCents($order['amountCents'], $order['currency'], $order['paymentToken']);

            // 3) Create invoice
            $invoiceId = $this->invoices->create($order, $chargeId);

            // 4) Create shipment
            $shipmentId = $this->shipping->createShipment($order['orderId']);

            $this->audit->log('checkout.succeeded', compact('chargeId','invoiceId','shipmentId'));
            return compact('chargeId','invoiceId','shipmentId');

        } catch (\Throwable $e) {
            // Compensating actions (very common in real facades!)
            $this->audit->log('checkout.failed', ['error' => $e->getMessage()]);
            if ($chargeId) {
                $this->payments->refund($chargeId, $order['amountCents']);
            }
            if ($reservationId) {
                $this->inventory->release($reservationId);
            }
            throw new CheckoutException('Order failed: '.$e->getMessage(), 0, $e);
        }
    }
}


$facade = new CheckoutFacade(
    new InventoryService(),
    new PaymentGateway(),
    new InvoiceService(),
    new ShippingService(),
    new AuditLogger()
);

$result = $facade->placeOrder([
    'orderId'     => 'ORD-1001',
    'sku'         => 'BOOK-123',
    'qty'         => 1,
    'amountCents' => 2599,
    'currency'    => 'EUR',
    'paymentToken'=> 'tok_visa_xxx',
]);

print_r($result);
