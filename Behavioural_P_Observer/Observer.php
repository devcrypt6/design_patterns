<?php
declare(strict_types=1);

/**
 * A WeatherStation (Subject) changes temperature; displays/loggers (Observers) react.
 * 
 */


/** Observer API */
interface Observer {
    public function update(Subject $subject): void; // "pull" model
}

/** Subject API */
interface Subject {
    public function attach(Observer $o): void;
    public function detach(Observer $o): void;
    public function notify(): void;
}

/** Concrete Subject */
final class WeatherStation implements Subject
{
    private array $observers = [];
    private float $temperature = 20.0;

    public function setTemperature(float $t): void
    {
        $this->temperature = $t;
        $this->notify();
    }
    public function getTemperature(): float { return $this->temperature; }

    public function attach(Observer $o): void { $this->observers[] = $o; }
    public function detach(Observer $o): void {
        $this->observers = array_filter($this->observers, fn($x) => $x !== $o);
    }
    public function notify(): void {
        foreach ($this->observers as $o) { $o->update($this); }
    }
}

/** Concrete Observers */
final class PhoneDisplay implements Observer {
    public function update(Subject $subject): void {
        if ($subject instanceof WeatherStation) {
            echo "[Phone] Temp: {$subject->getTemperature()}°C\n";
        }
    }
}

final class FileLogger implements Observer {
    public function update(Subject $subject): void {
        if ($subject instanceof WeatherStation) {
            echo "[Log] Temperature changed to {$subject->getTemperature()}°C\n";
        }
    }
}

/** Demo */
$station = new WeatherStation();

$phone   = new PhoneDisplay();
$logger  = new FileLogger();

$station->attach($phone);
$station->attach($logger);

$station->setTemperature(23.4);
$station->setTemperature(19.8);
