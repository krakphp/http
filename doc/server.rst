Server
======

Http Servers are responsible for creating a request, feeding it to the http handler, and then emitting the response.

.. code-block:: php

    <?php

    $serve = Krak\Mw\Http\server();
    $serve(function($req) {
        return $resp;
    });

Every server has the following interface::

    function(callable $handler)

Where the ``$handler`` is a function that accepts a PSR7 request and returns a PSR7 response.

API
~~~

function diactorosServer(Diactoros\\Response\\EmitterInterface $emitter = null, callable $req_factory = null)
    Run the app using Diactoros PSR7 system. The ``$req_factory`` is just a callable that returns a PSR7 request.
function server()
    Return the default server which is the ``diactorosServer``
