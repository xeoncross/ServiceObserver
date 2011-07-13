## PHP Service Locator and System Event Observer

This is a test using some of PHP's magic methods to create a global service
object which can be used to store services and alert observers in a memory
efficient way.

This class supports creating objects on the fly, lazy-loading, closures, and
object instances using singleton and factory patterns.

The best part is that entities your system needs can be added and removed at
run time to insure you never have extra objects floating around your application
using memory when they are not needed.
