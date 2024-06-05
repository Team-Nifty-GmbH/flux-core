<?php

use FluxErp\Http\Controllers\AuthController;
use FluxErp\Http\Middleware\PortalMiddleware;
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

Route::middleware(['web', PortalMiddleware::class])
    ->domain(config('flux.portal_domain'))
    ->name('portal.')
    ->group(function () {
        Route::get('/icons/{name}/{variant?}', IconController::class)
            ->where('variant', '(outline|solid)')
            ->name('icons');
        Route::get('/login', Login::class)
            ->middleware(['guest:address'])
            ->name('login');
        Route::post('/login', [AuthController::class, 'authenticatePortal'])
            ->middleware(['guest:address']);
        Route::any('/logout', Logout::class)
            ->name('logout');

        Route::middleware(['auth:address', 'permission'])->group(function () {
            Route::get('/', FluxErp\Livewire\Portal\Dashboard::class)
                ->name('dashboard');
            Route::get('/calendar', FluxErp\Livewire\Portal\Calendar::class)
                ->name('calendar');
            Route::get('/files', FluxErp\Livewire\Portal\Files::class)
                ->name('files');
            Route::get('/my-profile', FluxErp\Livewire\Portal\Profile::class)
                ->name('my-profile');
            Route::get('/orders/{id}', FluxErp\Livewire\Portal\OrderDetail::class)
                ->name('orders.id');
            Route::get('/orders', FluxErp\Livewire\Portal\Orders::class)
                ->name('orders');
            Route::get('/product/{id}', FluxErp\Livewire\Portal\Product::class)
                ->name('product');
            Route::get('/profiles/{id?}', FluxErp\Livewire\Portal\Profile::class)
                ->name('profiles.id?');
            Route::get('/serial-numbers', FluxErp\Livewire\Portal\SerialNumbers::class)
                ->name('serial-numbers');
            Route::get('/service/{serialNumberId?}', FluxErp\Livewire\Portal\Service::class)
                ->name('service');
            Route::get('/tickets', FluxErp\Livewire\Portal\Ticket\Tickets::class)
                ->name('tickets');
            Route::get('/tickets/{id}', FluxErp\Livewire\Portal\Ticket\Ticket::class)
                ->name('tickets.id');

            Route::get('/media/{media}/{filename}', function (Media $media) {
                return $media;
            });
        });
    });
