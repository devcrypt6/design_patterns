# Iterator Design Pattern

It work with Collection of Data types like List, Stack, Tree or etc...

lets you traverse elements of a collection without exposing its underlying representation.

PHP usages a consistent API current(), next(), valid(), etc.

In PHP you'll usually implement `Iterator` or `IteratorAggregate`, or use generators (`yield`) for lazy iteration.


### Core PHP pieces you'll see

1. `Iterator` - you implement the cursor yourself (`current`, `key`, `next`, `rewind`, `valid`).
2. `IteratorAggregate` - you return another iterator from `getIterator()` (easiest for custom collections).
3. `Traversable` - marker interface (you don’t implement it directly).
4. SPL helpers - `ArrayIterator`, `FilterIterator`, `RecursiveIteratorIterator`, etc.
5. Generators - functions using `yield` to create lazy iterators with almost no boilerplate.