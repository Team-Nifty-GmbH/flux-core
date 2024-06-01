<?php

use FluxErp\Livewire\Portal\Auth\Login;
use FluxErp\Livewire\Portal\Auth\Logout;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use TeamNiftyGmbH\DataTable\Controllers\IconController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/icons/{name}/{variant?}', IconController::class)
    ->where('variant', '(outline|solid)')
    ->name('icons');
Route::get('/login', Login::class)
    ->middleware(['guest:address'])
    ->name('login');
Route::any('/logout', Logout::class)
    ->name('logout');

Route::middleware(['auth:address', 'permission'])->group(function () {
    Route::get('/', FluxErp\Livewire\Portal\Dashboard::class)
        ->name('dashboard')
        ->registersMenuItem(icon: 'home', order: -9999);
    Route::get('/calendar', FluxErp\Livewire\Portal\Calendar::class)
        ->name('calendar')
        ->registersMenuItem(icon: 'calendar');
    Route::get('/files', FluxErp\Livewire\Portal\Files::class)
        ->name('files')
        ->registersMenuItem(icon: 'folder-open');
    Route::get('/my-profile', FluxErp\Livewire\Portal\Profile::class)
        ->name('my-profile');
    Route::get('/orders/{id}', FluxErp\Livewire\Portal\OrderDetail::class)
        ->name('orders.id');
    Route::get('/orders', FluxErp\Livewire\Portal\Orders::class)
        ->name('orders')
        ->registersMenuItem(icon: 'shopping-bag');
    Route::get('/product/{id}', FluxErp\Livewire\Portal\Product::class)
        ->name('product');
    Route::get('/profiles/{id?}', FluxErp\Livewire\Portal\Profile::class)
        ->name('profiles.id?');
    Route::get('/serial-numbers', FluxErp\Livewire\Portal\SerialNumbers::class)
        ->name('serial-numbers')
        ->registersMenuItem(icon: 'tag');
    Route::get('/service/{serialNumberId?}', FluxErp\Livewire\Portal\Service::class)
        ->name('service');
    Route::get('/tickets', FluxErp\Livewire\Portal\Ticket\Tickets::class)
        ->name('tickets')
        ->registersMenuItem(icon: 'wrench-screwdriver');
    Route::get('/tickets/{id}', FluxErp\Livewire\Portal\Ticket\Ticket::class)
        ->name('tickets.id');

    Route::get('/media/{media}/{filename}', function (Media $media) {
        return $media;
    });
});
