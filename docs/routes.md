You can override routes by adding the same name and uri in your projects web.php file.

```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:web'])->group(function () {
    Route::get('/order', \App\Http\Livewire\Order\Order::class);
});
```

This will override the route `/order` with the livewire component `\App\Http\Livewire\Order\Order::class`.

## Adding new routes

You can add new routes by adding them to your projects web.php file.

```php
<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:web'])->group(function () {
    Route::get('/my-module', \App\Http\Livewire\MyModule::class)
        ->registersMenuItem();
});
```

This will add a new route `/my-module` with the livewire component `\App\Http\Livewire\MyModule::class` and register a new menu item with the name `my-module` and the title `My module`.

A new Permission will automatically be created with the name `my-module` and the title `My module` after running `php artisan init:permissions`.
