<p align="center"><a href="https://team-nifty.com" target="_blank"><img src="public/pwa/images/icons-vector.svg" width="120"></a></p>
<h1 align="center">FluxERP</h1>

## Requirements

- PHP 8.4+
- Laravel 13+
- MySQL 8.4+
- Meilisearch
- Redis

### 1. Installation

Remove the welcome route from `routes/web.php`.

#### Laravel 13 Configuration

Laravel 13 introduces stricter serialization defaults that are incompatible with FluxERP. You must adjust the following settings:

In `config/cache.php`, allow class unserialization (FluxERP caches Eloquent models):

```php
'serializable_classes' => true,
```

In `config/session.php`, switch session serialization from JSON to PHP (FluxERP stores PHP objects like Collections in the session):

```php
'serialization' => 'php',
```

#### Storage Link

Add the following to your `config/filesystem.php` config file:

```php
'links' => [
    ...
    public_path('vendor/team-nifty-gmbh/flux') => base_path('vendor/team-nifty-gmbh/flux-erp/public'),
],
```

Then link the assets:

```bash
php artisan storage:link
```

#### Seeder

If you want to use seeders add the following to your `DatabaseSeeder.php` file:

```php
$this->call(\FluxErp\Database\Seeders\FluxSeeder::class);
```

### 2. Development

If you want to develop for flux-erp you should publish the docker files (this runs nginx instead of artisan serve):

```bash
php artisan vendor:publish --tag="flux-docker"
```

Alternatively you can change your `compose.yaml` file to use the flux-erp docker files from the vendor folder:

```yaml
laravel.test:
    build:
        context: ./vendor/team-nifty-gmbh/flux-erp/docker/8.5
    ...
```

If you have already built the Docker images, you should rebuild them:

```bash
sail build --no-cache
```

### 3. Extending FluxERP

FluxERP is designed to be extended with custom packages. You can create your own packages in a local `packages/` folder at the project root.

#### Creating a new package

1. Create your package directory:

```bash
mkdir -p packages/my-package/src
```

2. Add a `composer.json` to your package:

```json
{
    "name": "your-vendor/my-package",
    "description": "My FluxERP extension",
    "type": "library",
    "autoload": {
        "psr-4": {
            "YourVendor\\MyPackage\\": "src/"
        }
    },
    "require": {
        "team-nifty-gmbh/flux-erp": "^1.0 || dev-main"
    },
    "extra": {
        "laravel": {
            "providers": [
                "YourVendor\\MyPackage\\MyPackageServiceProvider"
            ]
        }
    }
}
```

3. Register the package as a path repository in your project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/my-package"
        }
    ],
    "require": {
        "your-vendor/my-package": "@dev"
    }
}
```

4. Run `composer update` to symlink the package into `vendor/`.

This approach lets you develop packages locally with live changes while keeping them as independent Composer packages that can be published later.

### 4. Running tests

```bash
cd vendor/team-nifty-gmbh/flux-erp
composer install
composer test
```

Tests are organized into the following suites: Browser, Feature, Livewire, Unit. You can run individual suites:

```bash
composer test-feature
composer test-livewire
composer test-browser
```

## 4. Websockets (Reverb)

FluxERP uses [Laravel Reverb](https://laravel.com/docs/reverb) for real-time broadcasting.

### Local Development

For local development with Sail, add the following to your `.env`:

```dotenv
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=0.0.0.0
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Production with nginx

For production, Reverb should run behind nginx with SSL. Your `.env` should look like this:

```dotenv
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=your.domain.com
REVERB_SCHEME=https
REVERB_PORT=443
```

Your nginx config should proxy websocket connections to Reverb:

```nginx
map $http_upgrade $type {
    default "web";
    websocket "wss";
}

server {
    root /var/www/your.domain.com/public;

    charset utf-8;
    error_page 404 /index.php;

    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Frame-Options "SAMEORIGIN";

    index index.php;
    server_name your.domain.com;

    location / {
        try_files /nonexistent @$type;
    }

    location @web {
        try_files $uri $uri/ /index.php?$args;
    }

    location @wss {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_read_timeout 60;
        proxy_connect_timeout 60;
        proxy_redirect off;

        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_cache_bypass $http_upgrade;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.5-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }

    listen [::]:443 ssl ipv6only=on;
    listen 443 ssl;
    ssl_certificate /etc/letsencrypt/live/your.domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your.domain.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
}

server {
    if ($host = your.domain.com) {
        return 301 https://$host$request_uri;
    }

    listen 80 default_server;
    listen [::]:80 default_server;
    server_name your.domain.com;
    return 404;
}
```

If you have only one instance running you can let Reverb handle connections directly without nginx on port 8080.
