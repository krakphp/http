<?php

namespace Krak\Mw\Http\Package {
    function std(array $config = []) {
        return new Std\StdPackage($config);
    }
}

namespace Krak\Mw\Http\Package\Std {
    function stdExceptionHandler($rf) {
        return function($req, $exception) use ($rf) {
            return $rf(500, ['Content-Type' => 'text/plain'], (string) $exception);
        };
    }

    function stdNotFoundHandler($rf) {
        return function($req, $result) use ($rf) {
            $headers = ['Content-Type' => 'text/plain'];
            if ($result->status_code == 405) {
                $headers['Allow'] = implode(", ", $result->allowed_methods);
            }

            return $rf(
                $result->status_code,
                $headers,
                $result->status_code == 405 ? '405 Method Not Allowed' : '404 Not Found'
            );
        };
    }
}
