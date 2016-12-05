Standard Package
================

The standard package brings a lot of the functionality to the Http App system.

It defines the required container serviers/parameters and also provides several stacks to enable routing/response marshalers/action invokers and so on.

StdServiceProvider
~~~~~~~~~~~~~~~~~~

Here all of the defined services and parameters

routes
    instance of RouteGroup
response_factory
    instance of ResponseFactory
dispatcher_factory
    a dispatcher factory
event_emitter
    evenement event emitter instance
freezer
    ``Krak\Mw\Http\Freezer`` instance
server
    ``Krak\Mw\Http\Server`` instance
stacks.exception_handler
    Mw Stack
stacks.invoke_action
    Mw Stack
stacks.not_found_handler
    Mw Stack
stacks.marshal_response
    Mw Stack
stacks.http
    Mw Stack

API
~~~

TODO
