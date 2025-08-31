# State Design Patterns

lets an object alter its behavior when its internal state changes. It appears as if the object changed its class.

Its Generally used on Global state management Patterns like Redux, Vuex, NgRx.

```
Client → Context (delegates) → Current State
                     ↑                 |
                 setState(new …)  ←───┘ (transitions)

```