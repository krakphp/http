Web Server Configuration
========================

All of the `Silex Web Server Configuration`_ will work for the Mw Http system.

.. _`Silex Web Server Configuration`: http://silex.sensiolabs.org/doc/master/web_servers.html

NGINX
~~~~~

.. code-block:: nginx

    server {
        server_name domain.tld www.domain.tld;
        root /var/www/project/web;

        location / {
            # try to serve file directly, fallback to front controller
            try_files $uri /app.php$is_args$args;
        }

        location ~ ^/app\.php(/|$) {
            # the ubuntu default
            fastcgi_pass   unix:/var/run/php/phpX.X-fpm.sock;
            # for running on centos
            #fastcgi_pass   unix:/var/run/php-fpm/www.sock;

            fastcgi_split_path_info ^(.+\.php)(/.*)$;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            fastcgi_param HTTPS off;

            # Prevents URIs that include the front controller. This will 404:
            # http://domain.tld/index.php/some-path
            # Enable the internal directive to disable URIs like this
            # internal;
        }

        #return 404 for all php files as we do have a front controller
        location ~ \.php$ {
            return 404;
        }

        error_log /var/log/nginx/project_error.log;
        access_log /var/log/nginx/project_access.log;
    }

PHP
~~~

You can start up your apps with the PHP development web server.

.. code-block:: php

    // web/app.php
    $filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
    if (php_sapi_name() === 'cli-server' && is_file($filename)) {
        return false;
    }

    $app = require __DIR__.'/../src/app.php';
    $app->run();
