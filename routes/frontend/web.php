<?php

use FluxErp\Livewire\Calendars\Calendar;
use FluxErp\Livewire\Contacts\Contact;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\CommissionList;
use FluxErp\Livewire\DataTables\ContactList;
use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Livewire\DataTables\ProductList;
use FluxErp\Livewire\DataTables\ProjectTasksList;
use FluxErp\Livewire\DataTables\SerialNumberList;
use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Livewire\DataTables\TransactionList;
use FluxErp\Livewire\Order\Order;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Product\Product;
use FluxErp\Livewire\Product\SerialNumber\SerialNumber;
use FluxErp\Livewire\Project\Project;
use FluxErp\Livewire\Project\ProjectList;
use FluxErp\Livewire\Settings\AdditionalColumns;
use FluxErp\Livewire\Settings\Categories;
use FluxErp\Livewire\Settings\Clients;
use FluxErp\Livewire\Settings\Countries;
use FluxErp\Livewire\Settings\Currencies;
use FluxErp\Livewire\Settings\CustomerPortal;
use FluxErp\Livewire\Settings\DiscountGroups;
use FluxErp\Livewire\Settings\Languages;
use FluxErp\Livewire\Settings\Logs;
use FluxErp\Livewire\Settings\Notifications;
use FluxErp\Livewire\Settings\OrderTypes;
use FluxErp\Livewire\Settings\Permissions;
use FluxErp\Livewire\Settings\PriceLists;
use FluxErp\Livewire\Settings\Profile;
use FluxErp\Livewire\Settings\TicketTypes;
use FluxErp\Livewire\Settings\Translations;
use FluxErp\Livewire\Settings\Users;
use FluxErp\Livewire\Ticket\Ticket;
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
    Route::name('projects.')->prefix('projects')
        ->group(function () {
            Route::permanentRedirect('/', '/')->registersMenuItem(icon: 'briefcase');
            Route::get('/list', ProjectList::class)->name('projects')->registersMenuItem();
            Route::get('/project-tasks', ProjectTasksList::class)
                ->name('project-tasks')
                ->registersMenuItem(icon: 'briefcase');
            Route::get('/{id}', Project::class)->name('id');
        });

    Route::name('orders.')->prefix('orders')
        ->group(function () {
            Route::permanentRedirect('/', '/')->registersMenuItem(icon: 'shopping-bag');
            Route::get('/list', OrderList::class)->name('orders')->registersMenuItem();
            Route::get('/order-positions/list', OrderPositionList::class)->name('order-positions')
                ->registersMenuItem();
            Route::get('/{id}', Order::class)->name('id');
        });

    Route::get('/tickets', TicketList::class)->name('tickets')->registersMenuItem(icon: 'wrench-screwdriver');
    Route::get('/tickets/{id}', Ticket::class)->name('tickets.id');

    Route::name('products.')->prefix('products')
        ->group(function () {
            Route::permanentRedirect('/', '/')->registersMenuItem(icon: 'square-3-stack-3d');
            Route::get('/list', ProductList::class)->name('products')->registersMenuItem();
            Route::get('/serial-numbers', SerialNumberList::class)->name('serial-numbers')->registersMenuItem();
            Route::get('/serial-numbers/{id?}', SerialNumber::class)->name('serial-numbers.id?');
            Route::get('/{id}', Product::class)->name('id');
        });

    Route::name('accounting.')->prefix('accounting')
        ->group(function () {
            Route::permanentRedirect('/', '/')->registersMenuItem(icon: 'banknotes');
            Route::get('/commissions', CommissionList::class)->name('commissions')->registersMenuItem();
            Route::get('/transactions', TransactionList::class)->name('transactions')->registersMenuItem();
        });

    Route::get('/my-profile', Profile::class)->name('my-profile');

    Route::get('/media/{media}/{filename}', function (Media $media) {
        return $media;
    });

    Route::name('settings.')->prefix('settings')
        ->group(function () {
            Route::permanentRedirect('/', '/')->registersMenuItem(icon: 'cog', order: 9999);
            Route::get('/additional-columns', AdditionalColumns::class)
                ->name('additional-columns')
                ->registersMenuItem();
            Route::get('/categories', Categories::class)
                ->name('categories')
                ->registersMenuItem();
            Route::get('/clients', Clients::class)
                ->name('clients')
                ->registersMenuItem();
            Route::get('/clients/{client}/customer-portal', CustomerPortal::class)
                ->name('customer-portal');
            Route::get('/countries', Countries::class)->name('countries')->registersMenuItem();
            Route::get('/currencies', Currencies::class)->name('currencies')->registersMenuItem();
            Route::get('/discount-groups', DiscountGroups::class)->name('discount-groups')->registersMenuItem();
            Route::get('/languages', Languages::class)->name('languages')->registersMenuItem();
            Route::get('/logs', Logs::class)->name('logs')->registersMenuItem();
            Route::get('/notifications', Notifications::class)->name('notifications')->registersMenuItem();
            Route::get('/order-types', OrderTypes::class)->name('order-types')->registersMenuItem();
            Route::get('/permissions', Permissions::class)->name('permissions')->registersMenuItem();
            Route::get('/price-lists', PriceLists::class)->name('price-lists')->registersMenuItem();
            Route::get('/ticket-types', TicketTypes::class)->name('ticket-types')->registersMenuItem();
            Route::get('/translations', Translations::class)->name('translations')->registersMenuItem();
            Route::get('/users', Users::class)->name('users')->registersMenuItem();
        });

    Route::name('search')->prefix('search')->group(function () {
        Route::any('/{model}', SearchController::class)->where('model', '(.*)');
    });
});
