<?php
/**
 * Decorator Design Pattern
 * 
 * Should be Followed Open/Closed: extend behavior without touching existing code.
 * 
 */



/**
 * Core Feature to provide Beverages
 */
interface Beverage {
    public function getDescription(): string;
    public function cost(): float;
}

/**
 * Let's Consider as a Base of making a coffee withour any Adds-On.
 * 
 * ConcreteComponent
 */
final class Espresso implements Beverage {
    public function getDescription(): string {
        return 'Espresso';
    }
    public function cost(): float {
        return 2.00;
    }
}


/**
 * Base Decorator (optional but convenient)
 */
abstract class BeverageDecorator implements Beverage {
    public function __construct(protected Beverage $beverage) {}
}

/**
 * Let's say Adds-On
 * 
 * ConcreteDecorators
 */
final class Milk extends BeverageDecorator {
    public function getDescription(): string {
        return $this->beverage->getDescription() . ', Milk';
    }
    public function cost(): float {
        return $this->beverage->cost() + 0.30;
    }
}

final class Soy extends BeverageDecorator {
    public function getDescription(): string {
        return $this->beverage->getDescription() . ', Soy';
    }
    public function cost(): float {
        return $this->beverage->cost() + 0.40;
    }
}

final class Whip extends BeverageDecorator {
    public function getDescription(): string {
        return $this->beverage->getDescription() . ', Whip';
    }
    public function cost(): float {
        return $this->beverage->cost() + 0.20;
    }
}


// ---------- Demo ----------
function printOrder(Beverage $b): void {
    echo $b->getDescription() . ' → €' . number_format($b->cost(), 2) . PHP_EOL;
}

$order1 = new Espresso();
printOrder($order1); // Espresso → €2.00

$order2 = new Milk(new Espresso());
printOrder($order2); // Espresso, Milk → €2.30

$order3 = new Whip(new Milk(new Espresso()));
printOrder($order3); // Espresso, Milk, Whip → €2.50

$order4 = new Soy(new Whip(new Milk(new Espresso())));
printOrder($order4); // Espresso, Milk, Whip, Soy → €2.90



/**
 * Let's suppose you want to add more Adds-On like Hafer Milk
 * 
 * So, we will just add New HapferMilk class and apply Adds-On without changing existing code.
 */
final class HaferMilk extends BeverageDecorator {
    public function getDescription(): string {
        return $this->beverage->getDescription() . ', Hafer Milk';
    }
    public function cost(): float {
        return $this->beverage->cost() + 0.80;
    }
}

$order4 = new Soy(new Whip(new HaferMilk(new Espresso())));
printOrder($order4); // Espresso, Hafer Milk, Whip, Soy → €2.80
