.. Mw Http documentation master file, created by
   sphinx-quickstart on Sun Nov 27 18:12:29 2016.
   You can adapt this file completely to your liking, but it should at least
   contain the root `toctree` directive.

Welcome to Mw Http's documentation!
===================================

Mw Http is a web framework designed around Middleware (Mw). The core of the system is just a stack of http middleware that accept PSR-7 requests and return PSR-7 responses.

The system was designed to be simple in design, yet powerful in nature. One of the main features of the Mw\\Http framework is the Package system. Similar to Symfony Bundles, they allow incredible extension to the framework structure allowing developers to create any type of extension large or small with ease.

Installation
~~~~~~~~~~~~

Install via composer at ``krak/mw-http``

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
   web-server-integration
