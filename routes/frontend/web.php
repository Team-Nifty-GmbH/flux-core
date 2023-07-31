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
use FluxErp\Http\Livewire\Project\Project;
use FluxErp\Http\Livewire\Project\ProjectList;
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
    Route::get('/', Dashboard::class)->name('dashboard')->registersMenuItem(icon: 'home', order: -9999);
    Route::get('/calendars', Calendar::class)->name('calendars')->registersMenuItem(icon: 'calendar');
    Route::get('/contacts', ContactList::class)->name('contacts')->registersMenuItem(icon: 'identification');
    Route::get('/contacts/{id?}', Contact::class)->name('contacts.id?');
    Route::get('/projects', ProjectList::class)->name('projects')->registersMenuItem(icon: 'briefcase');
    Route::get('/projects/{id}', Project::class)->name('projects.id?');
    Route::get('/orders', OrderList::class)->name('orders')->registersMenuItem(icon: 'shopping-bag');
    Route::get('/orders/{id}', Order::class)->name('orders.id?');
    Route::get('/tickets', TicketList::class)->name('tickets')->registersMenuItem(icon: 'wrench-screwdriver');
    Route::get('/tickets/{id}', Ticket::class)->name('tickets.id');

    Route::name('products.')->prefix('products')
        ->group(function () {
            Route::permanentRedirect('/', '/')->registersMenuItem(icon: 'square-3-stack-3d');
            Route::get('/list', ProductList::class)->name('products')->registersMenuItem();
            Route::get('/serial-numbers', SerialNumberList::class)->name('serial-numbers')->registersMenuItem();
            Route::get('/serial-numbers/{id?}', SerialNumber::class)->name('serial-numbers.id?');
            Route::get('/{id?}', Product::class)->name('id?');
        });

    Route::get('/my-profile/{id?}', Profile::class)->name('my-profile');

    Route::get('/media/{media}/{filename}', function (Media $media) {
        return $media;
    });

    Route::name('settings.')->prefix('settings')
        ->group(function () {
            Route::permanentRedirect('/', '/')->registersMenuItem(icon: 'cog', order: 9999);
            Route::get('/additional-columns', AdditionalColumns::class)
                ->name('additional-columns')
                ->registersMenuItem();
            Route::get('/calendars', Calendars::class)
                ->name('calendars')
                ->registersMenuItem();
            Route::get('/clients', Clients::class)
                ->name('clients')
                ->registersMenuItem();
            Route::get('/clients/{client}/customer-portal', CustomerPortal::class)
                ->name('customer-portal');
            Route::get('/countries', Countries::class)->name('countries')->registersMenuItem();
            Route::get('/currencies', Currencies::class)->name('currencies')->registersMenuItem();
            Route::get('/emails', Emails::class)->name('emails')->registersMenuItem();
            Route::get('/languages', Languages::class)->name('languages')->registersMenuItem();
            Route::get('/logs', Logs::class)->name('logs')->registersMenuItem();
            Route::get('/notifications', Notifications::class)->name('notifications')->registersMenuItem();
            Route::get('/order-types', OrderTypes::class)->name('order-types')->registersMenuItem();
            Route::get('/permissions', Permissions::class)->name('permissions')->registersMenuItem();
            Route::get('/ticket-types', TicketTypes::class)->name('ticket-types')->registersMenuItem();
            Route::get('/translations', Translations::class)->name('translations')->registersMenuItem();
            Route::get('/users', Users::class)->name('users')->registersMenuItem();
        });

    Route::name('search')->prefix('search')->group(function () {
        Route::any('/{model}', SearchController::class)->where('model', '(.*)');
    });
});
