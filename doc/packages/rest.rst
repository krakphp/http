REST Package
============

The rest package sits on top of the std package and adds functionality for building rest based JSON API's.

Services
~~~~~~~~

rest.error
    A service parameter which is a function with the following signature: ``function($code, $message, array $extra = [])``. This is used to generate error response objects.
rest.response_factory
    A jsonResponseFactory wrapped response factory from the app.
