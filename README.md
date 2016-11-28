# Mw Http

[![Documentation Status](https://readthedocs.org/projects/mw-http/badge/?version=latest)](http://mw-http.readthedocs.io/en/latest/?badge=latest)

Middlewares written for http applications

## Installation

Install via composer at `krak/mw-http`

## Usage

```php
<?php

use Krak\Mw\Http;

$app = new Http\App();
$app->get('/', function($req) {
    return "Hello World!";
});
$app->with(Http\Package\std());
$app->serve();
```

More documentation coming soon! For now look over the examples and source code for information.

## Documentation

View them at [http://mw-http.readthedocs.io/en/latest/](http://mw-http.readthedocs.io/en/latest/)

Or build them locally:

```bash
make doc
```

## Tests and Examples

Run tests via:

```bash
make test
```

View the test folder or example dir for examples on how the code is used.
