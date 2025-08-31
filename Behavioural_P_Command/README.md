# Command Behavioural Design Pattern

In simple terms, it converts Request into stand-alone Uniform Objects.

So, that we can do:
    1. queue, log, or retry actions
    2. undo/redo
    3. decouple the invoker (button, menu item, scheduler) from the receiver
    ```
    Client -> Invoker --(Command)  --> Receiver
                        executes()
                        undo()
    ```

