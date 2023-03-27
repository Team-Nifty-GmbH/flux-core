<?php

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
Route::view('/', 'flux::livewire.portal.auth.login')->name('portal.loginform');

Route::get('/icons/{name}/{variant?}', IconController::class)
    ->where('variant', '(outline|solid)')
    ->name('portal.icons');

Route::middleware(['auth:address'])->group(function () {
    Route::get('/', FluxErp\Http\Livewire\Portal\Dashboard::class)
        ->name('portal.dashboard');
    Route::get('/calendar', FluxErp\Http\Livewire\Portal\Calendar::class)
        ->name('portal.calendar');
    Route::get('/files', FluxErp\Http\Livewire\Portal\Files::class)
        ->name('portal.files');
    Route::get('/my-profile/{id?}', FluxErp\Http\Livewire\Portal\Profile::class)
        ->name('portal.my-profile');
    Route::get('/orders/{id}', FluxErp\Http\Livewire\Portal\OrderDetail::class)
        ->name('portal.orders.id');
    Route::get('/orders', FluxErp\Http\Livewire\Portal\Orders::class)
        ->name('portal.orders');
    Route::get('/product/{id}', FluxErp\Http\Livewire\Portal\Product::class)
        ->name('portal.product');
    Route::get('/profile/{id?}', FluxErp\Http\Livewire\Portal\Profile::class)
        ->name('portal.profile.id?');
    Route::get('/serial-numbers', FluxErp\Http\Livewire\Portal\SerialNumbers::class)
        ->name('portal.serial-numbers');
    Route::get('/service/{serialNumberId?}', FluxErp\Http\Livewire\Portal\Service::class)
        ->name('portal.service');
    Route::get('/tickets', FluxErp\Http\Livewire\Portal\Ticket\Tickets::class)
        ->name('portal.tickets');
    Route::get('/tickets/{id}', FluxErp\Http\Livewire\Portal\Ticket\Ticket::class)
        ->name('portal.tickets.id');

    Route::get('/media/{media}/{filename}', function (Media $media) {
        return $media;
    });
});
