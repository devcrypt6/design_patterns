# Template Method Design Patterns

Define the skeleton of an algorithm in a base class, and let subclasses fill in the steps.

```
Template method (fixed steps)
 ├─ step1()  ← subclass overrides
 ├─ step2()  ← subclass overrides
 └─ hook()   ← optional/default

```