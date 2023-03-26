<p align="center"><a href="https://team-nifty.com" target="_blank"><img src="https://user-images.githubusercontent.com/40495041/160839207-0e1593e0-ff3d-4407-b9d2-d3513c366ab9.svg" width="400"></a></p>

### 1. Installation
Remove the welcome route from `routes/web.php` and add the following route:

If you want to use seeders add the following to your DatabaseSeeder.php file:

```php
$this->call(\FluxErp\Database\Seeders\FluxSeeder::class);
```

### 2. Running tests
```bash
cd vendor/flux-erp
composer i
./vendor/bin/testbench package:test --parallel --configuration ./phpunit.xml
```
