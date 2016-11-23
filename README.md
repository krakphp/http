# Mw Http

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

## Packages

Packages are a way to extend an App. They can be as simple as adding a middleware to as complex as mounting an entire application on top of the app.

### Standard

### Pimple

### Rest

### Plates

### Create Your Own
