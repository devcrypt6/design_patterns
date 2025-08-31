# Observer Design Patterns
Also known as Event-Subscriber, Listener

Lets you defines subscribtion mechanism to notify multiple objects about any events that happen to the object ther're observing.

```
[Subject] --attach/detach--> [Observer A]
     |                        [Observer B]
     └─ on change → notify() → [Observer C]

```

It can be used to make you are appliacation Scalable.
