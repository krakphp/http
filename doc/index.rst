.. Mw Http documentation master file, created by
   sphinx-quickstart on Sun Nov 27 18:12:29 2016.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Welcome to Mw Http's documentation!
===================================

Middlewares written for http applications

Installation
~~~~~~~~~~~~

Install via composer at `krak/mw-http`

Basic Usage
~~~~~~~~~~~

.. code-block:: php

    <?php

    use Krak\Mw\Http;

    $app = new Http\App();
    $app->get('/', function($req) {
        return "Hello World!";
    });
    $app->with(Http\Package\std());
    $app->serve();

More documentation coming soon! For now look over the examples and source code for information.

.. toctree::
   :maxdepth: 2

   app
   packages
   server
   dispatcher
   events
   http-middleware
   invoke-action
   marshal-response
   resolve-argument
   response-factory
   router
