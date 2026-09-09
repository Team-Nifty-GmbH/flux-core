<p align="center"><a href="https://team-nifty.com" target="_blank"><img src="https://user-images.githubusercontent.com/40495041/160839207-0e1593e0-ff3d-4407-b9d2-d3513c366ab9.svg" width="400"></a></p>

# Flux ERP

Flux ERP is a modern, open-source ERP built on Laravel and Livewire. It ships as a
**Composer package** that you install into a Laravel application.

> [!IMPORTANT]
> Flux is a **package, not a standalone app.** That is why this repository has no
> `artisan` file and no `bootstrap/app.php` — those belong to the Laravel host
> application. You cannot run `php artisan …` inside this repository directly. Install
> it into a fresh Laravel app as shown below.

## Requirements

- PHP **8.4** (`bcmath`, `intl`, `json`, `pdo`, `zip`)
- MySQL 8 / MariaDB
- [Meilisearch](https://www.meilisearch.com/) — search (Laravel Scout)
- Composer 2

No Node.js or frontend build is needed on the project side. Flux serves its compiled
assets straight from the package at runtime.

## Installation

```bash
# 1. Create a fresh Laravel app
composer create-project laravel/laravel flux-app
cd flux-app

# 2. Require Flux (service provider, migrations, routes and assets auto-register)
composer require team-nifty-gmbh/flux-erp

# 3. Require the license package (provides the install wizard)
composer require team-nifty-gmbh/flux-license

# 4. Run the interactive install wizard
php artisan flux:install

# 5. Link storage for uploaded media
php artisan storage:link
```

Configure your database, Meilisearch and mail credentials in `.env` before running the
wizard, and remove the default welcome route from `routes/web.php` (Flux registers its
own).

The `php artisan flux:install` wizard guides you through the remaining setup
(migrations, roles and permissions, base data, payment types, and so on). If you
prefer to run the steps manually instead, use `php artisan migrate` and
`php artisan init:permissions`.

### Seeding base data

Add the seeder to `database/seeders/DatabaseSeeder.php`:

```php
$this->call(\FluxErp\Database\Seeders\FluxSeeder::class);
```

then run `php artisan db:seed`.

Serve the app with `php artisan serve` (or Sail) and log in.

## Frontend assets

Assets are compiled inside the package and served through its own `/flux/…` routes.
The `@fluxStyles` and `@fluxScripts` Blade directives inject them — add these to your
`<head>` and before `</body>` only if you use a custom layout instead of the shipped
one. Nothing to build or publish.

## Realtime (Laravel Reverb)

Flux uses [Laravel Reverb](https://reverb.laravel.com/) for realtime features.
Broadcasting credentials are read from `.env` at runtime, so no rebuild is required
when they change.

```bash
php artisan reverb:start
```

```dotenv
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=your.domain.com
REVERB_SCHEME=https
REVERB_PORT=443
```

In production run Reverb behind your web server as a reverse proxy so websocket
traffic reaches it on port 443.

## Publishing package resources (optional)

Only needed to override defaults:

| Tag                 | Publishes                                      |
| ------------------- | ---------------------------------------------- |
| `flux-config`       | `config/flux.php`                              |
| `flux-views`        | Blade views into `resources/views/vendor/flux` |
| `flux-translations` | Language files                                 |
| `flux-migrations`   | Migrations into `database/migrations`          |
| `flux-seeders`      | Seeders into `database/seeders`                |
| `flux-docker`       | Docker/Sail setup                              |

```bash
php artisan vendor:publish --tag=flux-config
```

## Development

Flux publishes a Docker/Sail setup that runs nginx instead of `artisan serve`:

```bash
php artisan vendor:publish --tag=flux-docker
```

Run the package test suite (uses [Testbench](https://github.com/orchestral/testbench))
from the package directory:

```bash
composer install
composer test
```

The test suite needs MySQL 8 or MariaDB as well. `phpunit.xml` pins `DB_CONNECTION=mysql`,
so the requirement above is not only about production. A local `.env` pointing at SQLite
aborts on the first migration that uses raw MySQL, before a single test has run. That abort
is not a failing test, it means the test database points at the wrong driver.

Give every session its own database. Parallel runs against one shared schema pull each
other's tables away through `RefreshDatabase`, which shows up as
`Table 'testing.migrations' doesn't exist` in the middle of a run:

```bash
mysql -e "CREATE DATABASE IF NOT EXISTS testing_mine;"
DB_DATABASE=testing_mine ./vendor/bin/testbench package:test
```

## Documentation

Guides for extending Flux live in [`docs`](./docs):
[routes](./docs/routes.md), [views](./docs/views.md), [widgets](./docs/widgets.md),
[print views](./docs/print-views.md), [monitorable jobs](./docs/monitorable-jobs.md),
[backend customization](./docs/customizing-backend.md).

## License

Flux ERP is open-sourced software licensed under the [MIT license](LICENSE.md).
