<?php

use FluxErp\Http\Livewire\Calendars\Calendar;
use FluxErp\Http\Livewire\Contacts\Contact;
use FluxErp\Http\Livewire\Dashboard\Dashboard;
use FluxErp\Http\Livewire\DataTables\ContactList;
use FluxErp\Http\Livewire\DataTables\ProductList;
use FluxErp\Http\Livewire\DataTables\SerialNumberList;
use FluxErp\Http\Livewire\DataTables\TicketList;
use FluxErp\Http\Livewire\Order\Order;
use FluxErp\Http\Livewire\Order\OrderList;
use FluxErp\Http\Livewire\Product\Product;
use FluxErp\Http\Livewire\Product\SerialNumber\SerialNumber;
use FluxErp\Http\Livewire\Settings\AdditionalColumns;
use FluxErp\Http\Livewire\Settings\Calendars;
use FluxErp\Http\Livewire\Settings\Clients;
use FluxErp\Http\Livewire\Settings\Countries;
use FluxErp\Http\Livewire\Settings\Currencies;
use FluxErp\Http\Livewire\Settings\CustomerPortal;
use FluxErp\Http\Livewire\Settings\Emails;
use FluxErp\Http\Livewire\Settings\Languages;
use FluxErp\Http\Livewire\Settings\Logs;
use FluxErp\Http\Livewire\Settings\Notifications;
use FluxErp\Http\Livewire\Settings\OrderTypes;
use FluxErp\Http\Livewire\Settings\Permissions;
use FluxErp\Http\Livewire\Settings\PriceLists;
use FluxErp\Http\Livewire\Settings\Profile;
use FluxErp\Http\Livewire\Settings\TicketTypes;
use FluxErp\Http\Livewire\Settings\Translations;
use FluxErp\Http\Livewire\Settings\Users;
use FluxErp\Http\Livewire\Ticket\Ticket;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use TeamNiftyGmbH\DataTable\Controllers\IconController;
use TeamNiftyGmbH\DataTable\Controllers\SearchController;

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

Route::middleware(['auth:web'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/calendars', Calendar::class)->name('calendars');
    Route::get('/contacts', ContactList::class)->name('contacts');
    Route::get('/contacts/{id?}', Contact::class)->name('contacts.id?');
    Route::get('/orders', OrderList::class)->name('orders');
    Route::get('/orders/{id}', Order::class)->name('orders.id?');
    Route::get('/tickets', TicketList::class)->name('tickets');
    Route::get('/tickets/{id}', Ticket::class)->name('tickets.id');

    Route::name('products.')->prefix('products')
        ->group(function () {
            Route::get('/list', ProductList::class)->name('products');
            Route::get('/serial-numbers', SerialNumberList::class)->name('serial-numbers');
            Route::get('/serial-numbers/{id?}', SerialNumber::class)->name('serial-numbers.id?');
            Route::get('/{id?}', Product::class)->name('id?');
        });

    Route::get('/my-profile/{id?}', Profile::class)->name('my-profile');

    Route::get('/media/{media}/{filename}', function (Media $media) {
        return $media;
    });

    Route::name('settings.')->prefix('settings')
        ->group(function () {
            Route::get('/additional-columns', AdditionalColumns::class)
                ->name('settings.additional-columns');
            Route::get('/calendars', Calendars::class)
                ->name('settings.calendars');
            Route::get('/clients', Clients::class)
                ->name('settings.clients');
            Route::get('/clients/{client}/customer-portal', CustomerPortal::class)
                ->name('settings.customer-portal');
            Route::get('/countries', Countries::class)->name('settings.countries');
            Route::get('/currencies', Currencies::class)->name('settings.currencies');
            Route::get('/emails', Emails::class)->name('settings.emails');
            Route::get('/languages', Languages::class)->name('settings.languages');
            Route::get('/logs', Logs::class)->name('settings.logs');
            Route::get('/notifications', Notifications::class)->name('settings.notifications');
            Route::get('/order-types', OrderTypes::class)->name('settings.order-types');
            Route::get('/permissions', Permissions::class)->name('settings.permissions');
            Route::get('/price-lists', PriceLists::class)->name('settings.price-lists');
            Route::get('/ticket-types', TicketTypes::class)->name('settings.ticket-types');
            Route::get('/translations', Translations::class)->name('settings.translations');
            Route::get('/users', Users::class)->name('settings.users');
        });

    Route::name('search')->prefix('search')->group(function () {
        Route::any('/{model}', SearchController::class)->where('model', '(.*)');
    });
});
