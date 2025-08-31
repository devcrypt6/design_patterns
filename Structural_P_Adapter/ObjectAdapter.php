<?php
/**
 * Object Adapter (simple version)
 *
 * Idea:
 * We want our app to use a single, clean interface (PaymentGateway),
 * even if different providers (PayPal, Stripe, etc.) have different SDKs.
 * So we wrap each SDK with an Adapter that matches our interface.
 */

/**
 * Adaptee / SDK service (example: PayPal)
 *
 * Pretend this is a third-party SDK we can’t change.
 * It talks to a remote API and returns a detailed array.
 */
class LegacyPaySdk
{
    public function pay(float $amount, string $currencyCode, array $meta): array
    {
        // Imagine a real API call here…

        // Example response from the provider
        return [
            'ok' => true,
            'id' => 'tx_'.bin2hex(random_bytes(4)),
            'amount' => $amount,
            'currency' => $currencyCode,
            'meta' => $meta,
        ];
    }
}

/**
 * Adapter
 *
 * Wraps the SDK and exposes the interface our app expects.
 * Converts our inputs/outputs to the SDK’s format.
 */
final class LegacyPayAdapter implements PaymentGateway
{
    public function __construct(private readonly LegacyPaySdk $sdk) {}

    public function charge(int $amountCents, string $currency, string $token): string
    {
        // Convert cents to decimal amount (e.g., 1999 → 19.99)
        $amountDecimal = $amountCents / 100;

        // Call the SDK, normalizing currency and passing our token as meta
        $resp = $this->sdk->pay(
            $amountDecimal,
            strtoupper($currency),
            ['source_token' => $token]
        );

        // Turn the provider’s response into our expected result
        if (!($resp['ok'] ?? false)) {
            throw new \RuntimeException('LegacyPay charge failed');
        }

        return (string)$resp['id'];
    }
}

/**
 * Target interface (what the app expects)
 *
 * Any payment provider must implement this.
 */
interface PaymentGateway {
    public function charge(int $amountCents, string $currency, string $token): string;
}

/**
 * Client code
 *
 * The app only knows about PaymentGateway.
 * We pass in the specific SDK via our adapter.
 */
$gateway = new LegacyPayAdapter(new LegacyPaySdk());
$txId = $gateway->charge(1999, 'eur', 'tok_visa');
echo "Paid. TX = $txId\n";

// Run: php .\Structural_P_Adapter\ObjectAdapter.php
// Example output: Paid. TX = tx_xxxxxxxx
