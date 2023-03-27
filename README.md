<p align="center"><a href="https://team-nifty.com" target="_blank"><img src="https://user-images.githubusercontent.com/40495041/160839207-0e1593e0-ff3d-4407-b9d2-d3513c366ab9.svg" width="400"></a></p>

### 1. Installation
Remove the welcome route from `routes/web.php` and add the following route.

link the flux-erp assets
```bash
php artisan storage:link
```
This will create a symlink in `public/flux` to `vendor/team-nifty-gmbh/flux/public` which is where the flux-erp assets are stored.

If you want to use seeders add the following to your DatabaseSeeder.php file:

```php
$this->call(\FluxErp\Database\Seeders\FluxSeeder::class);
```

Because vite includes the pusher data into the build process its neccessary to rebuild the assets after the installation.

```bash
vite build
```
Please keep in mind to do so after setting the pusher credentials in the .env file.


### 2. Running tests
```bash
cd vendor/flux-erp
composer i
./vendor/bin/testbench package:test --parallel --configuration ./phpunit.dist.xml
```

keep in mind that the tests
