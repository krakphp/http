Response Factory
================

Response Factories are functions that generate responses. This allows middleware libraries to not worry about
what specific PSR7 framework is being used, they just utilize the response factory stored in the app instead.

A response factory follows this interface::

    ResponseInterface function($status_code, array $headers = [], $body = null)

Where ``ResponseInterface`` is ``Psr\Http\Message\ResponseInterface``

API
~~~

responseFactory()
    Returns the default response factory which is just the ``diactorosResponseFactory``
defaultResponseFactory(ResposneInterface $resp)
    Always returns this specific response object instance
guzzleResponseFactory()
    Returns a response factory that uses the Guzzle PSR7 library
diactorosResponseFactory()
    Default response factory that uses the zend diactoros library to return the
    resposne.
jsonResponseFactory($rf, $encode_opts = 0)
    json decorator which will json encode the data and set the json content type. ``$rf`` should be another response factory,
    and the ``$encode_opts`` are any options to be passed into ``json_encode``
htmlResponseFactory($rf)
    html decorator which will set the text/html content-type on the response.
textResponseFactory($rf)
    text decorator which will set the text/plain content-type on the response
