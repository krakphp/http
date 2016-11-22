<?php

namespace Krak\Mw\Http\Routing;

class DispatchResult
{
    public $status_code;
    public $matched_route;
    /** used for 405 responses */
    public $allowed_methods;

    public function __construct($status_code) {
        $this->status_code = $status_code;
    }

    public static function create200(MatchedRoute $matched_route) {
        $res = new self(200);
        $res->matched_route = $matched_route;
        return $res;
    }

    public static function create404() {
        return new self(404);
    }

    public static function create405($allowed_methods = []) {
        $res = new self(405);
        $res->allowed_methods = $allowed_methods;
        return $res;
    }
}
