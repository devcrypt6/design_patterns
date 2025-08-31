<?php
/**
 * Again, let's take example of Notifications
 * 
 * Different Channnels should be implemnet Channel interface to provide coherency
 * 
 */
interface Channel
{
    public function send(string $title, string $message): void;
}

/**
 * Consider as Implementors
 * 
 * We create multiple Channels
*/
final class EmailChannel implements Channel
{
    public function __construct(private string $from = 'noreply@example.com') {}

    public function send(string $title, string $message): void
    {
        // Pretend mail() or a mailer library:
        echo "[EMAIL from {$this->from}] {$title}: {$message}\n";
    }
}

final class SmsChannel implements Channel
{
    public function __construct(private string $senderId = 'PeerApp') {}

    public function send(string $title, string $message): void
    {
        // Pretend SMS provider call:
        echo "[SMS {$this->senderId}] {$title}: {$message}\n";
    }
}

final class PushChannel implements Channel
{
    public function send(string $title, string $message): void
    {
        // Pretend FCM/APNs:
        echo "[PUSH] {$title}: {$message}\n";
    }
}



abstract class Notification
{
    public function __construct(protected Channel $channel) {}

    // Allow switching implementations at runtime if needed:
    public function setChannel(Channel $channel): void
    {
        $this->channel = $channel;
    }

    // The stable API clients call:
    final public function notify(string $title, string $message): void
    {
        [$t, $m] = $this->format($title, $message);
        $this->channel->send($t, $m);
    }

    // Each refined abstraction can format/augment content differently
    abstract protected function format(string $title, string $message): array;
}


/** Refined Abstractions */
final class UrgentNotification extends Notification
{
    protected function format(string $title, string $message): array
    {
        return ["⚠️ URGENT: {$title}", strtoupper($message)];
    }
}

final class MarketingNotification extends Notification
{
    public function __construct(Channel $channel, private string $utm = 'utm_source=peerapp') {
        parent::__construct($channel);
    }

    protected function format(string $title, string $message): array
    {
        $msg = "{$message}\n\nUnsubscribe in app settings. {$this->utm}";
        return [$title, $msg];
    }
}

final class SystemNotification extends Notification
{
    protected function format(string $title, string $message): array
    {
        return ["[System] {$title}", $message];
    }
}



// Usage
$urgentOverSms = new UrgentNotification(new SmsChannel(senderId: 'ALERTS'));
$urgentOverSms->notify('Payment Failure', 'User payout job halted on node EU-1.');

$marketingEmail = new MarketingNotification(new EmailChannel('promo@example.com'), utm: 'utm_campaign=summer25');
$marketingEmail->notify('25% Off', 'Get your PRO now.');


$sysPush = new SystemNotification(new PushChannel());
$sysPush->notify('Deploy complete', 'Version 1.4.2 is live.');


// Switch the implementor at runtime:
$sysPush->setChannel(new EmailChannel('ops@example.com'));
$sysPush->notify('Deploy complete', 'Also emailed to on-call.');
