<?php

use FluxErp\Http\Controllers\AuthController;
use FluxErp\Http\Controllers\MediaController;
use FluxErp\Http\Middleware\PortalMiddleware;
use FluxErp\Livewire\Portal\Auth\Login;
use FluxErp\Livewire\Portal\Auth\Logout;
use FluxErp\Livewire\Portal\Auth\ResetPassword;
use FluxErp\Livewire\Portal\Calendar;
use FluxErp\Livewire\Portal\Dashboard;
use FluxErp\Livewire\Portal\Files;
use FluxErp\Livewire\Portal\OrderDetail;
use FluxErp\Livewire\Portal\Orders;
use FluxErp\Livewire\Portal\Profile;
use FluxErp\Livewire\Portal\Service;
use FluxErp\Livewire\Portal\Shop\Checkout;
use FluxErp\Livewire\Portal\Shop\CheckoutFinish;
use FluxErp\Livewire\Portal\Shop\ProductDetail;
use FluxErp\Livewire\Portal\Shop\ProductList;
use FluxErp\Livewire\Portal\Shop\Watchlists;
use FluxErp\Livewire\Portal\Ticket\Ticket;
use FluxErp\Livewire\Portal\Ticket\Tickets;
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
    ->group(function (): void {

        Route::get('/icons/{name}/{variant?}', IconController::class)
            ->where('variant', '(outline|solid)')
            ->name('icons');

        Route::middleware(['guest:address'])->group(function (): void {
            Route::get('/login', Login::class)
                ->name('login');
            Route::post('/login', [AuthController::class, 'authenticatePortal']);
            Route::get('/reset-password', ResetPassword::class)
                ->name('password.reset');
        });
        Route::any('/logout', Logout::class)
            ->name('logout');

        Route::middleware(['auth:address', 'permission'])->group(function (): void {
            Route::get('/', Dashboard::class)
                ->name('dashboard');
            Route::get('/calendar', Calendar::class)
                ->name('calendar');
            Route::get('/products', ProductList::class)
                ->name('products');
            Route::get('/products/{product}', ProductDetail::class)
                ->name('products.show');
            Route::get('/files', Files::class)
                ->name('files');
            Route::get('/my-profile', Profile::class)
                ->name('my-profile');
            Route::get('/orders/{id}', OrderDetail::class)
                ->name('orders.id');
            Route::get('/orders', Orders::class)
                ->name('orders');
            Route::get('/profiles/{id?}', Profile::class)
                ->name('profiles.id?');
            Route::get('/service/{serialNumberId?}', Service::class)
                ->name('service');
            Route::get('/tickets', Tickets::class)
                ->name('tickets');
            Route::get('/tickets/{id}', Ticket::class)
                ->name('tickets.id');
            Route::get('/watchlists', Watchlists::class)
                ->name('watchlists');
            Route::get('/checkout', Checkout::class)
                ->name('checkout');
            Route::get('/checkout-finish', CheckoutFinish::class)
                ->name('checkout-finish');

            Route::get('/media/{media}/{filename}', function (Media $media) {
                return $media;
            })->name('media');

            Route::any('/media/download-multiple', [MediaController::class, 'downloadMultiple'])
                ->name('media.download-multiple');
        });
    });
