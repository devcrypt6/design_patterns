# Adapter Design Patterns

It Also Known as Wrapper.

It's used Bind a Thrid party App without Modifying Original App.

```
Client ──> Target (what the client expects)
            ▲
            │ implements Target
         Adapter ──> Adaptee (existing/3rd-party/legacy)

```

It's also hide complexity of logic Under the hood and provide simple implements points.


### Two Types:
1. Object adapter: It's like a thrid part Library (Refers example of ObjectAdapter.php)
2. Class Adapter: It's simple Inheritance (Refer example of ClassAdapter.php)


## More details: https://refactoring.guru/design-patterns/adapter