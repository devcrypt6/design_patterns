# Bride Design Patterns

It helps to Split large classes or set of closed related classes into two seperate hierarchy - Abstraction and Implementation. Which also can be developed seperatly.



Say you have a geometric `Shape` class with a pair of subclasses: `Circle` and `Square`. 
You want to extend this class hierarchy to incorporate colors, so you plan to create `Red` and `Blue` shape subclasses. 
However, since you already have two subclasses, you'll need to create four class combinations such as `BlueCircle` and `RedSquare`.

Adding new shape types and colors to the hierarchy will grow it exponentially. 
For example, to add a triangle shape you'd need to introduce two subclasses, one for each color. 
And after that, adding a new color would require creating three subclasses, one for each shape type. 
The further we go, the worse it becomes.


Bride Design Patterns will solve problem of growing Application into Two Directions.

Now, Let's take a another Real World example of `Notifications`.
Let's say, Differnt Notifications channels like `Email`, `SMS`, or `Push` should be implemented. 
Now, client want more changes like they wants to 
implmente different Mode of notification such `Urgent Notification`, `System Notification` or `Marketing Notification`, and 
in-addition, these modes should be combined with above channels as well.

So, problem is our application growing into Two dimensions ways.

Let's solve these problem.






