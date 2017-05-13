# starchphp/starch

Starch binds a bunch of PSR compatible components together to form a functioning micro-framework.

## Installation

The package isn't on packagist yet, so right now, add it to your `composer.json` first.
 
 ```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/starchphp/starch"
    }
]
 ```

Update

```bash
composer update starchphp/starch
```

## Usage


Create a new App, define routes, add middlewares and run the app.

```php
<?php

use Interop\Http\ServerMiddleware\DelegateInterface;
use Starch\App;
use Zend\Diactoros\Response;

require('../vendor/autoload.php');

$app = new App();

$app->get('/', function() {
    $response = new Response();
    $response->getBody()->write('Hello');

    return $response;
});

$app->add(function($request, DelegateInterface $next) {
    $response = $next->process($request);
    $response->getBody()->write(', world! ');

    return $response;
});

$app->run();

```

## Components used

The following components are used to provide a coherent whole

- [zendframework/zend-diactoros](https://github.com/zendframework/zend-diactoros) provides the PSR-7 interfaces
- [php-di/php-di](https://github.com/PHP-DI/PHP-DI) provides a PSR-11 container
- [mindplay/middleman](https://github.com/mindplay-dk/middleman) adds PSR-15 middleware dispatching
- [nikic/fast-route](https://github.com/nikic/FastRoute) is used for the routing