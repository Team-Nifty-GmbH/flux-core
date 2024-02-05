<?php

use FluxErp\Http\Controllers\PrintController;
use FluxErp\Http\Controllers\PushSubscriptionController;
use FluxErp\Http\Middleware\TrackVisits;
use FluxErp\Livewire\Accounting\DirectDebit;
use FluxErp\Livewire\Accounting\MoneyTransfer;
use FluxErp\Livewire\Accounting\TransactionList;
use FluxErp\Livewire\Calendars\Calendar;
use FluxErp\Livewire\Contact\Contact;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\AddressList;
use FluxErp\Livewire\DataTables\CommissionList;
use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Livewire\DataTables\PaymentRunList;
use FluxErp\Livewire\DataTables\ProductOptionGroupList;
use FluxErp\Livewire\DataTables\SerialNumberList;
use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Livewire\DataTables\WorkTimeList;
use FluxErp\Livewire\Mail\Mail;
use FluxErp\Livewire\Order\Order;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Product\Product;
use FluxErp\Livewire\Product\ProductList;
use FluxErp\Livewire\Product\SerialNumber\SerialNumber;
use FluxErp\Livewire\Project\Project;
use FluxErp\Livewire\Project\ProjectList;
use FluxErp\Livewire\Settings\AdditionalColumns;
use FluxErp\Livewire\Settings\BankConnections;
use FluxErp\Livewire\Settings\Categories;
use FluxErp\Livewire\Settings\Clients;
use FluxErp\Livewire\Settings\Countries;
use FluxErp\Livewire\Settings\Currencies;
use FluxErp\Livewire\Settings\CustomerPortal;
use FluxErp\Livewire\Settings\DiscountGroups;
use FluxErp\Livewire\Settings\Languages;
use FluxErp\Livewire\Settings\Logs;
use FluxErp\Livewire\Settings\MailAccounts;
use FluxErp\Livewire\Settings\Notifications;
use FluxErp\Livewire\Settings\OrderTypes;
use FluxErp\Livewire\Settings\PaymentTypes;
use FluxErp\Livewire\Settings\Permissions;
use FluxErp\Livewire\Settings\PriceLists;
use FluxErp\Livewire\Settings\Profile;
use FluxErp\Livewire\Settings\Scheduling;
use FluxErp\Livewire\Settings\SerialNumberRanges;
use FluxErp\Livewire\Settings\TicketTypes;
use FluxErp\Livewire\Settings\Translations;
use FluxErp\Livewire\Settings\Users;
use FluxErp\Livewire\Settings\VatRates;
use FluxErp\Livewire\Settings\Warehouses;
use FluxErp\Livewire\Settings\WorkTimeTypes;
use FluxErp\Livewire\Task\Task;
use FluxErp\Livewire\Task\TaskList;
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

Route::middleware(['auth:web', 'permission'])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard')->registersMenuItem(icon: 'home', order: -9999);

    Route::middleware(TrackVisits::class)->group(function () {
        Route::get('/mail', Mail::class)->name('mail')->registersMenuItem(icon: 'envelope');
        Route::get('/calendars', Calendar::class)->name('calendars')->registersMenuItem(icon: 'calendar');
        Route::get('/contacts', AddressList::class)->name('contacts')->registersMenuItem(icon: 'identification');
        Route::get('/contacts/{id?}', Contact::class)->name('contacts.id?');

        Route::name('orders.')->prefix('orders')
            ->group(function () {
                Route::permanentRedirect('/', '/')
                    ->withoutMiddleware(TrackVisits::class)
                    ->registersMenuItem(icon: 'shopping-bag');
                Route::get('/list', OrderList::class)->name('orders')->registersMenuItem();
                Route::get('/order-positions/list', OrderPositionList::class)->name('order-positions')
                    ->registersMenuItem();
                Route::get('/{id}', Order::class)->where('id', '[0-9]+')->name('id');
            });

        Route::get('/tasks', TaskList::class)->name('tasks')->registersMenuItem(icon: 'clipboard-document-list');
        Route::get('/tasks/{id}', Task::class)->name('tasks.id');
        Route::get('/tickets', TicketList::class)->name('tickets')->registersMenuItem(icon: 'wrench-screwdriver');
        Route::get('/tickets/{id}', Ticket::class)->name('tickets.id');
        Route::get('/projects', ProjectList::class)->name('projects')->registersMenuItem(icon: 'briefcase');
        Route::get('/projects/{id}', Project::class)->name('projects.id');

        Route::name('products.')->prefix('products')
            ->group(function () {
                Route::permanentRedirect('/', '/')
                    ->withoutMiddleware(TrackVisits::class)
                    ->registersMenuItem(icon: 'square-3-stack-3d');
                Route::get('/list', ProductList::class)->name('products')->registersMenuItem();
                Route::get('/serial-numbers', SerialNumberList::class)->name('serial-numbers')->registersMenuItem();
                Route::get('/serial-numbers/{id?}', SerialNumber::class)->name('serial-numbers.id?');
                Route::get('/{id}', Product::class)->where('id', '[0-9]+')->name('id');
            });

        Route::name('accounting.')->prefix('accounting')
            ->group(function () {
                Route::permanentRedirect('/', '/')
                    ->withoutMiddleware(TrackVisits::class)
                    ->registersMenuItem(icon: 'banknotes');
                Route::get('/work-times', WorkTimeList::class)->name('work-times')->registersMenuItem();
                Route::get('/commissions', CommissionList::class)->name('commissions')->registersMenuItem();
                Route::get('/transactions', TransactionList::class)->name('transactions')->registersMenuItem();
                Route::get('/direct-debit', DirectDebit::class)->name('direct-debit')->registersMenuItem();
                Route::get('/money-transfer', MoneyTransfer::class)->name('money-transfer')->registersMenuItem();
                Route::get('/payment-runs', PaymentRunList::class)->name('payment-runs')->registersMenuItem();
            });

        Route::get('/my-profile', Profile::class)->name('my-profile');

        Route::name('settings.')->prefix('settings')
            ->group(function () {
                Route::permanentRedirect('/', '/')
                    ->withoutMiddleware(TrackVisits::class)
                    ->registersMenuItem(icon: 'cog', order: 9999);
                Route::get('/additional-columns', AdditionalColumns::class)
                    ->name('additional-columns')
                    ->registersMenuItem();
                Route::get('/categories', Categories::class)
                    ->name('categories')
                    ->registersMenuItem();
                Route::get('/product-option-groups', ProductOptionGroupList::class)
                    ->name('product-option-groups')
                    ->registersMenuItem();
                Route::get('/clients', Clients::class)
                    ->name('clients')
                    ->registersMenuItem();
                Route::get('/bank-connections', BankConnections::class)
                    ->name('bank-connections')
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
                Route::get('/mail-accounts', MailAccounts::class)->name('mail-accounts')->registersMenuItem();
                Route::get('/work-time-types', WorkTimeTypes::class)->name('work-time-types')->registersMenuItem();
                Route::get('/vat-rates', VatRates::class)->name('vat-rates')->registersMenuItem();
                Route::get('/payment-types', PaymentTypes::class)->name('payment-types')->registersMenuItem();
                Route::get('/warehouses', Warehouses::class)->name('warehouses')->registersMenuItem();
                Route::get('/serial-number-ranges', SerialNumberRanges::class)
                    ->name('serial-number-ranges')
                    ->registersMenuItem();
                Route::get('/scheduling', Scheduling::class)->name('scheduling')->registersMenuItem();
            });
    });

    Route::post('/push-subscription', [PushSubscriptionController::class, 'upsert']);

    Route::get('/media/{media}/{filename}', function (Media $media) {
        return $media;
    });
});

Route::group(['middleware' => ['auth:web']], function () {
    Route::any('/search/{model}', SearchController::class)
        ->where('model', '(.*)')
        ->name('search');
    Route::match(['get', 'post'], '/print/render', [PrintController::class, 'render'])->name('print.render');
    Route::match(['get', 'post'], '/print/pdf', [PrintController::class, 'renderPdf']);
});
