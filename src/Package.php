<?php

namespace Krak\Mw\Http;

/** Main interface for extending the App.

    The with allows a simple interface for extending ALL aspects of the App passed
    in, this can be used to add middlewares, listeners, or services into the app. */
interface Package {
    public function with(App $app);
}
