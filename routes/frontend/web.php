<?php

use FluxErp\Http\Controllers\AuthController;
use FluxErp\Http\Controllers\PrintController;
use FluxErp\Http\Controllers\PushSubscriptionController;
use FluxErp\Http\Controllers\SearchController;
use FluxErp\Http\Middleware\NoAuth;
use FluxErp\Http\Middleware\TrackVisits;
use FluxErp\Livewire\Accounting\DirectDebit;
use FluxErp\Livewire\Accounting\MoneyTransfer;
use FluxErp\Livewire\Accounting\PaymentReminder;
use FluxErp\Livewire\Accounting\TransactionList;
use FluxErp\Livewire\Auth\Login;
use FluxErp\Livewire\Auth\Logout;
use FluxErp\Livewire\Auth\ResetPassword;
use FluxErp\Livewire\Calendars\Calendar;
use FluxErp\Livewire\Cart\Watchlists;
use FluxErp\Livewire\Contact\CommunicationList;
use FluxErp\Livewire\Contact\Contact;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\AddressList;
use FluxErp\Livewire\DataTables\CommissionList;
use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Livewire\DataTables\PaymentRunList;
use FluxErp\Livewire\DataTables\PurchaseInvoiceList;
use FluxErp\Livewire\DataTables\TicketList;
use FluxErp\Livewire\DataTables\WorkTimeList;
use FluxErp\Livewire\InstallWizard;
use FluxErp\Livewire\Mail\Mail;
use FluxErp\Livewire\Media\Media as MediaGrid;
use FluxErp\Livewire\Order\Order;
use FluxErp\Livewire\Order\OrderList;
use FluxErp\Livewire\Order\OrderListByOrderType;
use FluxErp\Livewire\Product\Product;
use FluxErp\Livewire\Product\ProductList;
use FluxErp\Livewire\Product\SerialNumber\SerialNumber;
use FluxErp\Livewire\Product\SerialNumber\SerialNumberList;
use FluxErp\Livewire\Project\Project;
use FluxErp\Livewire\Project\ProjectList;
use FluxErp\Livewire\Settings\ActivityLogs;
use FluxErp\Livewire\Settings\AdditionalColumns;
use FluxErp\Livewire\Settings\AddressTypes;
use FluxErp\Livewire\Settings\BankConnections;
use FluxErp\Livewire\Settings\Categories;
use FluxErp\Livewire\Settings\Clients;
use FluxErp\Livewire\Settings\ContactOrigins;
use FluxErp\Livewire\Settings\Countries;
use FluxErp\Livewire\Settings\Currencies;
use FluxErp\Livewire\Settings\CustomerPortal;
use FluxErp\Livewire\Settings\DiscountGroups;
use FluxErp\Livewire\Settings\FailedJobs;
use FluxErp\Livewire\Settings\Industries;
use FluxErp\Livewire\Settings\Languages;
use FluxErp\Livewire\Settings\LedgerAccounts;
use FluxErp\Livewire\Settings\Logs;
use FluxErp\Livewire\Settings\MailAccounts;
use FluxErp\Livewire\Settings\Notifications;
use FluxErp\Livewire\Settings\OrderTypes;
use FluxErp\Livewire\Settings\PaymentReminderTexts;
use FluxErp\Livewire\Settings\PaymentTypes;
use FluxErp\Livewire\Settings\Permissions;
use FluxErp\Livewire\Settings\Plugins;
use FluxErp\Livewire\Settings\PriceLists;
use FluxErp\Livewire\Settings\Printers;
use FluxErp\Livewire\Settings\PrintJobs;
use FluxErp\Livewire\Settings\ProductOptionGroups;
use FluxErp\Livewire\Settings\ProductPropertyGroups;
use FluxErp\Livewire\Settings\Profile;
use FluxErp\Livewire\Settings\QueueMonitor;
use FluxErp\Livewire\Settings\Scheduling;
use FluxErp\Livewire\Settings\SerialNumberRanges;
use FluxErp\Livewire\Settings\Settings;
use FluxErp\Livewire\Settings\Tags;
use FluxErp\Livewire\Settings\TicketTypes;
use FluxErp\Livewire\Settings\Translations;
use FluxErp\Livewire\Settings\Units;
use FluxErp\Livewire\Settings\UserEdit;
use FluxErp\Livewire\Settings\Users;
use FluxErp\Livewire\Settings\VatRates;
use FluxErp\Livewire\Settings\Warehouses;
use FluxErp\Livewire\Settings\WorkTimeTypes;
use FluxErp\Livewire\Task\Task;
use FluxErp\Livewire\Task\TaskList;
use FluxErp\Livewire\Ticket\Ticket;
use FluxErp\Models\Address;
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
Route::middleware('web')
    ->domain(config('flux.flux_url'))
    ->group(function () {
        Route::middleware(NoAuth::class)->get('/install', InstallWizard::class)
            ->name('flux.install');

        Route::get('/icons/{name}/{variant?}', IconController::class)
            ->where('variant', '(outline|solid)')
            ->name('icons');
        Route::middleware(['guest:web'])->group(function () {
            Route::get('/login', Login::class)
                ->name('login');
            Route::post('/login', [AuthController::class, 'authenticateWeb']);
            Route::get('/reset-password', ResetPassword::class)
                ->name('password.reset');
        });
        Route::post('/logout', Logout::class)
            ->name('logout');

        Route::middleware(['auth:web', 'permission'])->group(function () {
            Route::get('/', Dashboard::class)->name('dashboard');

            Route::middleware(TrackVisits::class)->group(function () {
                Route::get('/mail', Mail::class)->name('mail');
                Route::get('/calendars', Calendar::class)->name('calendars');

                Route::name('contacts.')->prefix('contacts')
                    ->group(function () {
                        Route::get('/', AddressList::class)->name('contacts');
                        Route::get('/{id?}', Contact::class)->where('id', '[0-9]+')->name('id?');
                        Route::get('/communications', CommunicationList::class)->name('communications');
                    });
                Route::get(
                    '/address/{address}',
                    fn (Address $address) => redirect()
                        ->route(
                            'contacts.id?',
                            [
                                'id' => $address->contact_id,
                                'address' => $address->getKey(),
                            ]
                        )
                )
                    ->name('address.id');

                Route::name('orders.')->prefix('orders')
                    ->group(function () {
                        Route::get('/list', OrderList::class)->name('orders');
                        Route::get('/list/{orderType}', OrderListByOrderType::class)->name('order-type');
                        Route::get('/order-positions/list', OrderPositionList::class)->name('order-positions');
                        Route::get('/{id}', Order::class)->where('id', '[0-9]+')->name('id');
                    });

                Route::get('/tasks', TaskList::class)->name('tasks');
                Route::get('/tasks/{id}', Task::class)->name('tasks.id');
                Route::get('/tickets', TicketList::class)->name('tickets');
                Route::get('/tickets/{id}', Ticket::class)->name('tickets.id');
                Route::get('/projects', ProjectList::class)->name('projects');
                Route::get('/projects/{id}', Project::class)->name('projects.id');

                Route::name('products.')->prefix('products')
                    ->group(function () {
                        Route::get('/list', ProductList::class)->name('products');
                        Route::get('/serial-numbers', SerialNumberList::class)->name('serial-numbers');
                        Route::get('/serial-numbers/{id?}', SerialNumber::class)->name('serial-numbers.id?');
                        Route::get('/{id}', Product::class)->where('id', '[0-9]+')->name('id');
                    });

                Route::name('accounting.')->prefix('accounting')
                    ->group(function () {
                        Route::get('/work-times', WorkTimeList::class)->name('work-times');
                        Route::get('/commissions', CommissionList::class)->name('commissions');
                        Route::get('/payment-reminders', PaymentReminder::class)->name('payment-reminders');
                        Route::get('/purchase-invoices', PurchaseInvoiceList::class)->name('purchase-invoices');
                        Route::get('/transactions', TransactionList::class)->name('transactions');
                        Route::get('/direct-debit', DirectDebit::class)->name('direct-debit');
                        Route::get('/money-transfer', MoneyTransfer::class)->name('money-transfer');
                        Route::get('/payment-runs', PaymentRunList::class)->name('payment-runs');
                    });

                Route::get('/my-profile', Profile::class)->name('my-profile');

                Route::get('/settings', Settings::class)->name('settings');
                Route::name('settings.')->prefix('settings')
                    ->group(function () {
                        Route::get('/activity-logs', ActivityLogs::class)->name('activity-logs');
                        Route::get('/additional-columns', AdditionalColumns::class)->name('additional-columns');
                        Route::get('/address-types', AddressTypes::class)->name('address-types');
                        Route::get('/bank-connections', BankConnections::class)->name('bank-connections');
                        Route::get('/categories', Categories::class)->name('categories');
                        Route::get('/clients', Clients::class)->name('clients');
                        Route::get('/clients/{client}/customer-portal', CustomerPortal::class)->name('customer-portal');
                        Route::get('/contact-origins', ContactOrigins::class)->name('contact-origins');
                        Route::get('/countries', Countries::class)->name('countries');
                        Route::get('/currencies', Currencies::class)->name('currencies');
                        Route::get('/discount-groups', DiscountGroups::class)->name('discount-groups');
                        Route::get('/failed-jobs', FailedJobs::class)->name('failed-jobs');
                        Route::get('/industries', Industries::class)->name('industries');
                        Route::get('/languages', Languages::class)->name('languages');
                        Route::get('/ledger-accounts', LedgerAccounts::class)->name('ledger-accounts');
                        Route::get('/logs', Logs::class)->name('logs');
                        Route::get('/mail-accounts', MailAccounts::class)->name('mail-accounts');
                        Route::get('/notifications', Notifications::class)->name('notifications');
                        Route::get('/order-types', OrderTypes::class)->name('order-types');
                        Route::get('/payment-reminder-texts', PaymentReminderTexts::class)->name('payment-reminder-texts');
                        Route::get('/payment-types', PaymentTypes::class)->name('payment-types');
                        Route::get('/permissions', Permissions::class)->name('permissions');
                        Route::get('/plugins', Plugins::class)->name('plugins');
                        Route::get('/price-lists', PriceLists::class)->name('price-lists');
                        Route::get('/print-jobs', PrintJobs::class)->name('print-jobs');
                        Route::get('/printers', Printers::class)->name('printers');
                        Route::get('/product-option-groups', ProductOptionGroups::class)->name('product-option-groups');
                        Route::get('/product-properties', ProductPropertyGroups::class)->name('product-properties');
                        Route::get('/queue-monitor', QueueMonitor::class)->name('queue-monitor');
                        Route::get('/scheduling', Scheduling::class)->name('scheduling');
                        Route::get('/serial-number-ranges', SerialNumberRanges::class)->name('serial-number-ranges');
                        Route::get('/tags', Tags::class)->name('tags');
                        Route::get('/ticket-types', TicketTypes::class)->name('ticket-types');
                        Route::get('/translations', Translations::class)->name('translations');
                        Route::get('/units', Units::class)->name('units');
                        Route::get('/users', Users::class)->name('users');
                        Route::get('/users/{user}', UserEdit::class)->name('users.edit');
                        Route::get('/vat-rates', VatRates::class)->name('vat-rates');
                        Route::get('/warehouses', Warehouses::class)->name('warehouses');
                        Route::get('/work-time-types', WorkTimeTypes::class)->name('work-time-types');
                    });

                Route::get('/media', MediaGrid::class)
                    ->name('media-grid');

                Route::get('/watchlists', Watchlists::class)
                    ->name('watchlists');
            });

            Route::post('/push-subscription', [PushSubscriptionController::class, 'upsert']);

            Route::get('/media/{media}/{filename}', function (Media $media) {
                return $media;
            })->name('media');
        });

        Route::group(['middleware' => ['auth:web']], function () {
            Route::any('/search/{model}', SearchController::class)
                ->where('model', '(.*)')
                ->name('search');
            Route::match(['get', 'post'], '/print/render', [PrintController::class, 'render'])->name('print.render');
            Route::match(['get', 'post'], '/print/pdf', [PrintController::class, 'renderPdf']);
        });

        Route::middleware('signed')->group(function () {
            Route::get('/media-private/{media}/{filename}', function (Media $media) {
                return $media;
            })->name('media.private');
        });
    });
