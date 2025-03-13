<?php

use FluxErp\Actions\AdditionalColumn\CreateAdditionalColumn;
use FluxErp\Actions\AdditionalColumn\CreateValueList;
use FluxErp\Actions\AdditionalColumn\DeleteAdditionalColumn;
use FluxErp\Actions\AdditionalColumn\DeleteValueList;
use FluxErp\Actions\AdditionalColumn\UpdateAdditionalColumn;
use FluxErp\Actions\AdditionalColumn\UpdateValueList;
use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Address\DeleteAddress;
use FluxErp\Actions\Address\GenerateLoginToken;
use FluxErp\Actions\Address\UpdateAddress;
use FluxErp\Actions\AddressType\CreateAddressType;
use FluxErp\Actions\AddressType\DeleteAddressType;
use FluxErp\Actions\AddressType\UpdateAddressType;
use FluxErp\Actions\BankConnection\CreateBankConnection;
use FluxErp\Actions\BankConnection\DeleteBankConnection;
use FluxErp\Actions\BankConnection\UpdateBankConnection;
use FluxErp\Actions\Calendar\CreateCalendar;
use FluxErp\Actions\Calendar\DeleteCalendar;
use FluxErp\Actions\Calendar\UpdateCalendar;
use FluxErp\Actions\CalendarEvent\CreateCalendarEvent;
use FluxErp\Actions\CalendarEvent\DeleteCalendarEvent;
use FluxErp\Actions\CalendarEvent\UpdateCalendarEvent;
use FluxErp\Actions\Category\CreateCategory;
use FluxErp\Actions\Category\DeleteCategory;
use FluxErp\Actions\Category\UpdateCategory;
use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Actions\Comment\DeleteComment;
use FluxErp\Actions\Comment\UpdateComment;
use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Contact\DeleteContact;
use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Actions\ContactBankConnection\CreateContactBankConnection;
use FluxErp\Actions\ContactBankConnection\DeleteContactBankConnection;
use FluxErp\Actions\ContactBankConnection\UpdateContactBankConnection;
use FluxErp\Actions\ContactOption\CreateContactOption;
use FluxErp\Actions\ContactOption\DeleteContactOption;
use FluxErp\Actions\ContactOption\UpdateContactOption;
use FluxErp\Actions\ContactOrigin\CreateContactOrigin;
use FluxErp\Actions\ContactOrigin\DeleteContactOrigin;
use FluxErp\Actions\ContactOrigin\UpdateContactOrigin;
use FluxErp\Actions\Country\CreateCountry;
use FluxErp\Actions\Country\DeleteCountry;
use FluxErp\Actions\Country\UpdateCountry;
use FluxErp\Actions\CountryRegion\CreateCountryRegion;
use FluxErp\Actions\CountryRegion\DeleteCountryRegion;
use FluxErp\Actions\CountryRegion\UpdateCountryRegion;
use FluxErp\Actions\Currency\CreateCurrency;
use FluxErp\Actions\Currency\DeleteCurrency;
use FluxErp\Actions\Currency\UpdateCurrency;
use FluxErp\Actions\Discount\CreateDiscount;
use FluxErp\Actions\Discount\DeleteDiscount;
use FluxErp\Actions\Discount\UpdateDiscount;
use FluxErp\Actions\EventSubscription\CreateEventSubscription;
use FluxErp\Actions\EventSubscription\DeleteEventSubscription;
use FluxErp\Actions\EventSubscription\UpdateEventSubscription;
use FluxErp\Actions\FormBuilderField\CreateFormBuilderField;
use FluxErp\Actions\FormBuilderField\DeleteFormBuilderField;
use FluxErp\Actions\FormBuilderField\UpdateFormBuilderField;
use FluxErp\Actions\FormBuilderFieldResponse\CreateFormBuilderFieldResponse;
use FluxErp\Actions\FormBuilderFieldResponse\DeleteFormBuilderFieldResponse;
use FluxErp\Actions\FormBuilderFieldResponse\UpdateFormBuilderFieldResponse;
use FluxErp\Actions\FormBuilderForm\CreateFormBuilderForm;
use FluxErp\Actions\FormBuilderForm\DeleteFormBuilderForm;
use FluxErp\Actions\FormBuilderForm\UpdateFormBuilderForm;
use FluxErp\Actions\FormBuilderResponse\CreateFormBuilderResponse;
use FluxErp\Actions\FormBuilderResponse\DeleteFormBuilderResponse;
use FluxErp\Actions\FormBuilderSection\CreateFormBuilderSection;
use FluxErp\Actions\FormBuilderSection\DeleteFormBuilderSection;
use FluxErp\Actions\FormBuilderSection\UpdateFormBuilderSection;
use FluxErp\Actions\Language\CreateLanguage;
use FluxErp\Actions\Language\DeleteLanguage;
use FluxErp\Actions\Language\UpdateLanguage;
use FluxErp\Actions\LanguageLine\CreateLanguageLine;
use FluxErp\Actions\LanguageLine\DeleteLanguageLine;
use FluxErp\Actions\LanguageLine\UpdateLanguageLine;
use FluxErp\Actions\LedgerAccount\CreateLedgerAccount;
use FluxErp\Actions\LedgerAccount\DeleteLedgerAccount;
use FluxErp\Actions\LedgerAccount\UpdateLedgerAccount;
use FluxErp\Actions\MailAccount\CreateMailAccount;
use FluxErp\Actions\MailAccount\DeleteMailAccount;
use FluxErp\Actions\MailAccount\UpdateMailAccount;
use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\DeleteMediaCollection;
use FluxErp\Actions\Media\DownloadMedia;
use FluxErp\Actions\Media\ReplaceMedia;
use FluxErp\Actions\Media\UpdateMedia;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Actions\Permission\DeletePermission;
use FluxErp\Actions\Permission\UpdateUserPermissions;
use FluxErp\Actions\PurchaseInvoicePosition\CreatePurchaseInvoicePosition;
use FluxErp\Actions\PurchaseInvoicePosition\DeletePurchaseInvoicePosition;
use FluxErp\Actions\PurchaseInvoicePosition\UpdatePurchaseInvoicePosition;
use FluxErp\Actions\Role\CreateRole;
use FluxErp\Actions\Role\DeleteRole;
use FluxErp\Actions\Role\UpdateRole;
use FluxErp\Actions\Role\UpdateRolePermissions;
use FluxErp\Actions\Role\UpdateRoleUsers;
use FluxErp\Actions\Role\UpdateUserRoles;
use FluxErp\Actions\SepaMandate\CreateSepaMandate;
use FluxErp\Actions\SepaMandate\DeleteSepaMandate;
use FluxErp\Actions\SepaMandate\UpdateSepaMandate;
use FluxErp\Actions\SerialNumber\CreateSerialNumber;
use FluxErp\Actions\SerialNumber\DeleteSerialNumber;
use FluxErp\Actions\SerialNumber\UpdateSerialNumber;
use FluxErp\Actions\SerialNumberRange\CreateSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\DeleteSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\UpdateSerialNumberRange;
use FluxErp\Actions\Setting\CreateSetting;
use FluxErp\Actions\Setting\UpdateSetting;
use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Actions\StockPosting\DeleteStockPosting;
use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Actions\Tag\DeleteTag;
use FluxErp\Actions\Tag\UpdateTag;
use FluxErp\Actions\Task\CreateTask;
use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Actions\Ticket\DeleteTicket;
use FluxErp\Actions\Ticket\ToggleTicketUser;
use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Actions\TicketType\CreateTicketType;
use FluxErp\Actions\TicketType\DeleteTicketType;
use FluxErp\Actions\TicketType\UpdateTicketType;
use FluxErp\Actions\Unit\CreateUnit;
use FluxErp\Actions\Unit\DeleteUnit;
use FluxErp\Actions\User\CreateUser;
use FluxErp\Actions\User\DeleteUser;
use FluxErp\Actions\User\UpdateUser;
use FluxErp\Actions\VatRate\CreateVatRate;
use FluxErp\Actions\VatRate\DeleteVatRate;
use FluxErp\Actions\VatRate\UpdateVatRate;
use FluxErp\Actions\Warehouse\CreateWarehouse;
use FluxErp\Actions\Warehouse\DeleteWarehouse;
use FluxErp\Actions\Warehouse\UpdateWarehouse;
use FluxErp\Actions\WorkTime\CreateWorkTime;
use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateWorkTime;
use FluxErp\Actions\WorkTimeType\CreateWorkTimeType;
use FluxErp\Actions\WorkTimeType\DeleteWorkTimeType;
use FluxErp\Actions\WorkTimeType\UpdateWorkTimeType;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Controllers\AuthController;
use FluxErp\Http\Controllers\BaseController;
use FluxErp\Http\Controllers\EventSubscriptionController;
use FluxErp\Http\Controllers\LockController;
use FluxErp\Http\Controllers\PermissionController;
use FluxErp\Http\Controllers\PrintController;
use FluxErp\Http\Controllers\RoleController;
use FluxErp\Http\Controllers\SettingController;
use FluxErp\Http\Middleware\SetAcceptHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('api')
    ->middleware(['throttle:api', SetAcceptHeaders::class])
    ->group(function (): void {
        Route::get('/media/{file_name}', DownloadMedia::class)->name('media.public');
        Route::post('/auth/token', [AuthController::class, 'authenticate']);

        Route::middleware(['auth:sanctum', 'abilities:user', 'localization', 'permission', 'api'])
            ->name('api.')
            ->group(function (): void {
                // Validate Token
                Route::get('/auth/token/validate', [AuthController::class, 'validateToken']);
                Route::post('/logout', [AuthController::class, 'logout']);

                // AdditionalColumns
                Route::get('/additional-columns/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\AdditionalColumn::class);
                Route::get('/additional-columns', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\AdditionalColumn::class);
                Route::post('/additional-columns', CreateAdditionalColumn::class);
                Route::put('/additional-columns', UpdateAdditionalColumn::class);
                Route::delete('/additional-columns/{id}', DeleteAdditionalColumn::class);

                // Addresses
                Route::get('/addresses/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Address::class);
                Route::get('/addresses', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Address::class);
                Route::post('/addresses', CreateAddress::class);
                Route::put('/addresses', UpdateAddress::class);
                Route::delete('/addresses/{id}', DeleteAddress::class);
                Route::post('/address/{id}/login-token', GenerateLoginToken::class);

                // AddressTypes
                Route::get('/address-types/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\AddressType::class);
                Route::get('/address-types', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\AddressType::class);
                Route::post('/address-types', CreateAddressType::class);
                Route::put('/address-types', UpdateAddressType::class);
                Route::delete('/address-types/{id}', DeleteAddressType::class);

                // BankConnections
                Route::get('/bank-connections/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\BankConnection::class);
                Route::get('/bank-connections', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\BankConnection::class);
                Route::post('/bank-connections', CreateBankConnection::class);
                Route::put('/bank-connections', UpdateBankConnection::class);
                Route::delete('/bank-connections/{id}', DeleteBankConnection::class);

                // Calendars
                Route::get('/calendars/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Calendar::class);
                Route::get('/calendars', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Calendar::class);
                Route::post('/calendars', CreateCalendar::class);
                Route::put('/calendars', UpdateCalendar::class);
                Route::delete('/calendars/{id}', DeleteCalendar::class);

                // CalendarEvents
                Route::get('/calendar-events/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\CalendarEvent::class);
                Route::get('/calendar-events', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\CalendarEvent::class);
                Route::post('/calendar-events', CreateCalendarEvent::class);
                Route::put('/calendar-events', UpdateCalendarEvent::class);
                Route::delete('/calendar-events/{id}', DeleteCalendarEvent::class);

                // Categories
                Route::get('/categories/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Category::class);
                Route::get('/categories', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Category::class);
                Route::post('/categories', CreateCategory::class);
                Route::put('/categories', UpdateCategory::class);
                Route::delete('/categories/{id}', DeleteCategory::class);

                // Clients
                Route::get('/clients/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Client::class);
                Route::get('/clients', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Client::class);

                // Comments
                Route::get('/{modelType}/comments/{id}', [FluxErp\Http\Controllers\CommentController::class, 'show'])->defaults('model', FluxErp\Models\Comment::class);
                Route::post('/comments', CreateComment::class);
                Route::put('/comments', UpdateComment::class);
                Route::delete('/comments/{id}', DeleteComment::class);

                // ContactBankConnections
                Route::get('/contact-bank-connections/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\ContactBankConnection::class);
                Route::get('/contact-bank-connections', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\ContactBankConnection::class);
                Route::post('/contact-bank-connections', CreateContactBankConnection::class);
                Route::put('/contact-bank-connections', UpdateContactBankConnection::class);
                Route::delete('/contact-bank-connections/{id}', DeleteContactBankConnection::class);

                // ContactOptions
                Route::get('/contact-options/{id}', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\ContactOption::class);
                Route::post('/contact-options', CreateContactOption::class);
                Route::put('/contact-options', UpdateContactOption::class);
                Route::delete('/contact-options', DeleteContactOption::class);

                // ContactOrigins
                Route::get('/contact-origins/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\ContactOrigin::class);
                Route::get('/contact-origins', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\ContactOrigin::class);
                Route::post('/contact-origins', CreateContactOrigin::class);
                Route::put('/contact-origins', UpdateContactOrigin::class);
                Route::delete('/contact-origins/{id}', DeleteContactOrigin::class);

                // Contacts
                Route::get('/contacts/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Contact::class);
                Route::get('/contacts', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Contact::class);
                Route::post('/contacts', CreateContact::class);
                Route::put('/contacts', UpdateContact::class);
                Route::delete('/contacts/{id}', DeleteContact::class);

                // Countries
                Route::get('/countries/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Country::class);
                Route::get('/countries', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Country::class);
                Route::post('/countries', CreateCountry::class);
                Route::put('/countries', UpdateCountry::class);
                Route::delete('/countries/{id}', DeleteCountry::class);

                // CountryRegions
                Route::get('/country-regions/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\CountryRegion::class);
                Route::get('/country-regions', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\CountryRegion::class);
                Route::post('/country-regions', CreateCountryRegion::class);
                Route::put('/country-regions', UpdateCountryRegion::class);
                Route::delete('/country-regions/{id}', DeleteCountryRegion::class);

                // Currencies
                Route::get('/currencies/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Currency::class);
                Route::get('/currencies', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Currency::class);
                Route::post('/currencies', CreateCurrency::class);
                Route::put('/currencies', UpdateCurrency::class);
                Route::delete('/currencies/{id}', DeleteCurrency::class);

                // Discounts
                Route::get('/discounts/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Discount::class);
                Route::get('/discounts', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Discount::class);
                Route::post('/discounts', CreateDiscount::class);
                Route::put('/discounts', UpdateDiscount::class);
                Route::delete('/discounts/{id}', DeleteDiscount::class);

                // Events
                Route::get('/events', [EventSubscriptionController::class, 'getEvents']);

                // FormBuilderForm
                Route::get('/form-builder/forms/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\FormBuilderForm::class);
                Route::get('/form-builder/forms', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\FormBuilderForm::class);
                Route::post('/form-builder/forms', CreateFormBuilderForm::class);
                Route::put('/form-builder/forms', UpdateFormBuilderForm::class);
                Route::delete('/form-builder/forms/{id}', DeleteFormBuilderForm::class);

                // FormBuilderSection
                Route::get('/form-builder/sections/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\FormBuilderSection::class);
                Route::get('/form-builder/sections', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\FormBuilderSection::class);
                Route::post('/form-builder/sections', CreateFormBuilderSection::class);
                Route::put('/form-builder/sections', UpdateFormBuilderSection::class);
                Route::delete('/form-builder/sections/{id}', DeleteFormBuilderSection::class);

                // FormBuilderField
                Route::get('/form-builder/fields/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\FormBuilderField::class);
                Route::get('/form-builder/fields', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\FormBuilderField::class);
                Route::post('/form-builder/fields', CreateFormBuilderField::class);
                Route::put('/form-builder/fields', UpdateFormBuilderField::class);
                Route::delete('/form-builder/fields/{id}', DeleteFormBuilderField::class);

                // FormBuilderResponse
                Route::get('/form-builder/responses/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\FormBuilderResponse::class);
                Route::get('/form-builder/responses', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\FormBuilderResponse::class);
                Route::post('/form-builder/responses', CreateFormBuilderResponse::class);
                Route::delete('/form-builder/responses/{id}', DeleteFormBuilderResponse::class);

                // FormBuilderFieldsResponse
                Route::get('/form-builder/fields-responses/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\FormBuilderFieldResponse::class);
                Route::get('/form-builder/fields-responses', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\FormBuilderFieldResponse::class);
                Route::post('/form-builder/fields-responses', CreateFormBuilderFieldResponse::class);
                Route::put('/form-builder/fields-responses', UpdateFormBuilderFieldResponse::class);
                Route::delete('/form-builder/fields-responses/{id}', DeleteFormBuilderFieldResponse::class);

                // Languages
                Route::get('/languages/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Language::class);
                Route::get('/languages', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Language::class);
                Route::post('/languages', CreateLanguage::class);
                Route::put('/languages', UpdateLanguage::class);
                Route::delete('/languages/{id}', DeleteLanguage::class);

                // LedgerAccounts
                Route::get('/ledger-accounts/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\LedgerAccount::class);
                Route::get('/ledger-accounts', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\LedgerAccount::class);
                Route::post('/ledger-accounts', CreateLedgerAccount::class);
                Route::put('/ledger-accounts', UpdateLedgerAccount::class);
                Route::delete('/ledger-accounts/{id}', DeleteLedgerAccount::class);

                // Locking
                Route::get('/user/locks', [LockController::class, 'showUserLocks']);
                Route::get('/locks', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Lock::class);
                Route::get('/{modelType}/lock', [LockController::class, 'lock']);

                // MailAccounts
                Route::get('/mail-accounts/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\MailAccount::class);
                Route::get('/mail-accounts', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\MailAccount::class);
                Route::post('/mail-accounts', CreateMailAccount::class);
                Route::put('/mail-accounts', UpdateMailAccount::class);
                Route::delete('/mail-accounts/{id}', DeleteMailAccount::class);

                // Media
                Route::get('/media/private/{id}', DownloadMedia::class)
                    ->withoutMiddleware(SetAcceptHeaders::class);
                Route::post('/media/{id}', ReplaceMedia::class);
                Route::post('/media', UploadMedia::class);
                Route::put('/media', UpdateMedia::class);
                Route::delete('/media/{id}', DeleteMedia::class);
                Route::delete('/media-collection', DeleteMediaCollection::class);

                // NotificationSettings
                Route::put('/notifications', FluxErp\Actions\NotificationSetting\UpdateNotificationSetting::class)
                    ->defaults('is_anonymous', true);
                Route::put('/user/notifications', FluxErp\Actions\NotificationSetting\UpdateNotificationSetting::class);

                // Orders
                Route::get('/orders/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Order::class);
                Route::get('/orders', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Order::class);
                Route::post('/orders', FluxErp\Actions\Order\CreateOrder::class);
                Route::put('/orders', FluxErp\Actions\Order\UpdateOrder::class);
                Route::put('/orders/{id}/toggle-lock', FluxErp\Actions\Order\ToggleLock::class);
                Route::delete('/orders/{id}', FluxErp\Actions\Order\DeleteOrder::class);

                // OrderPositions
                Route::get('/order-positions/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\OrderPosition::class);
                Route::get('/order-positions', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\OrderPosition::class);
                Route::post('/order-positions', FluxErp\Actions\OrderPosition\CreateOrderPosition::class);
                Route::post('/order-positions/fill', FluxErp\Actions\OrderPosition\FillOrderPositions::class);
                Route::put('/order-positions', FluxErp\Actions\OrderPosition\UpdateOrderPosition::class);
                Route::delete('/order-positions/{id}', FluxErp\Actions\OrderPosition\DeleteOrderPosition::class);

                // OrderTypes
                Route::get('/order-types/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\OrderType::class);
                Route::get('/order-types', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\OrderType::class);
                Route::post('/order-types', FluxErp\Actions\OrderType\CreateOrderType::class);
                Route::put('/order-types', FluxErp\Actions\OrderType\UpdateOrderType::class);
                Route::delete('/order-types/{id}', FluxErp\Actions\OrderType\DeleteOrderType::class);

                // PaymentReminders
                Route::get('/payment-reminders/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\PaymentReminder::class);
                Route::get('/payment-reminders', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\PaymentReminder::class);
                Route::post('/payment-reminders', FluxErp\Actions\PaymentReminder\CreatePaymentReminder::class);
                Route::put('/payment-reminders', FluxErp\Actions\PaymentReminder\UpdatePaymentReminder::class);
                Route::delete('/payment-reminders/{id}', FluxErp\Actions\PaymentReminder\DeletePaymentReminder::class);

                // PaymentReminderTexts
                Route::get('/payment-reminder-texts/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\PaymentReminderText::class);
                Route::get('/payment-reminder-texts', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\PaymentReminderText::class);
                Route::post('/payment-reminder-texts', FluxErp\Actions\PaymentReminderText\CreatePaymentReminderText::class);
                Route::put('/payment-reminder-texts', FluxErp\Actions\PaymentReminderText\UpdatePaymentReminderText::class);
                Route::delete('/payment-reminder-texts/{id}', FluxErp\Actions\PaymentReminderText\DeletePaymentReminderText::class);

                // PaymentRuns
                Route::get('/payment-runs/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\PaymentRun::class);
                Route::get('/payment-runs', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\PaymentRun::class);
                Route::post('/payment-runs', FluxErp\Actions\PaymentRun\CreatePaymentRun::class);
                Route::put('/payment-runs', FluxErp\Actions\PaymentRun\UpdatePaymentRun::class);
                Route::delete('/payment-runs/{id}', FluxErp\Actions\PaymentRun\DeletePaymentRun::class);

                // PaymentTypes
                Route::get('/payment-types/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\PaymentType::class);
                Route::get('/payment-types', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\PaymentType::class);
                Route::post('/payment-types', FluxErp\Actions\PaymentType\CreatePaymentType::class);
                Route::put('/payment-types', FluxErp\Actions\PaymentType\UpdatePaymentType::class);
                Route::delete('/payment-types/{id}', FluxErp\Actions\PaymentType\DeletePaymentType::class);

                // Permissions
                Route::get('/permissions', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Permission::class);
                Route::get('/permissions/user/{id}', [PermissionController::class, 'showUserPermissions']);
                Route::post('/permissions', FluxErp\Actions\Permission\CreatePermission::class);
                Route::put('/permissions/give', UpdateUserPermissions::class)->defaults('give', true);
                Route::put('/permissions/revoke', UpdateUserPermissions::class)->defaults('give', false);
                Route::put('/permissions/sync', UpdateUser::class)->defaults('sync', true);
                Route::delete('/permissions/{id}', DeletePermission::class);

                // Prices
                Route::get('/prices/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Price::class);
                Route::get('/prices', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Price::class);
                Route::post('/prices', FluxErp\Actions\Price\CreatePrice::class);
                Route::put('/prices', FluxErp\Actions\Price\UpdatePrice::class);
                Route::delete('/prices/{id}', FluxErp\Actions\Price\DeletePrice::class);

                // PriceLists
                Route::get('/price-lists/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\PriceList::class);
                Route::get('/price-lists', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\PriceList::class);
                Route::post('/price-lists', FluxErp\Actions\PriceList\CreatePriceList::class);
                Route::put('/price-lists', FluxErp\Actions\PriceList\UpdatePriceList::class);
                Route::delete('/price-lists/{id}', FluxErp\Actions\PriceList\DeletePriceList::class);

                // PrintPdf
                Route::post('/print/views', [PrintController::class, 'getPrintViews']);
                Route::post('/print/render', FluxErp\Actions\Printing::class)
                    ->defaults('html', true)
                    ->defaults('preview', false);
                Route::post('/print/pdf', FluxErp\Actions\Printing::class)
                    ->defaults('html', false);

                // Products
                Route::get('/products/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Product::class);
                Route::get('/products', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Product::class);
                Route::post('/products', FluxErp\Actions\Product\CreateProduct::class);
                Route::put('/products', FluxErp\Actions\Product\UpdateProduct::class);
                Route::delete('/products/{id}', FluxErp\Actions\Product\DeleteProduct::class);

                // Product bundle products
                Route::get('/product-bundle-products/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Pivots\ProductBundleProduct::class);
                Route::get('/product-bundle-products', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Pivots\ProductBundleProduct::class);
                Route::post('/product-bundle-products', FluxErp\Actions\Product\ProductBundleProduct\CreateProductBundleProduct::class);
                Route::put('/product-bundle-products', FluxErp\Actions\Product\ProductBundleProduct\UpdateProductBundleProduct::class);
                Route::delete('/product-bundle-products/{id}', FluxErp\Actions\Product\ProductBundleProduct\DeleteProductBundleProduct::class);

                // ProductCrossSellings
                Route::get('/product-cross-sellings/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\ProductCrossSelling::class);
                Route::get('/product-cross-sellings', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\ProductCrossSelling::class);
                Route::post('/product-cross-sellings', FluxErp\Actions\ProductCrossSelling\CreateProductCrossSelling::class);
                Route::put('/product-cross-sellings', FluxErp\Actions\ProductCrossSelling\UpdateProductCrossSelling::class);
                Route::delete('/product-cross-sellings/{id}', FluxErp\Actions\ProductCrossSelling\DeleteProductCrossSelling::class);

                // ProductOptions
                Route::get('/product-options/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\ProductOption::class);
                Route::get('/product-options', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\ProductOption::class);
                Route::post('/product-options', FluxErp\Actions\ProductOption\CreateProductOption::class);
                Route::put('/product-options', FluxErp\Actions\ProductOption\UpdateProductOption::class);
                Route::delete('/product-options/{id}', FluxErp\Actions\ProductOption\DeleteProductOption::class);

                // ProductOptionGroups
                Route::get('/product-option-groups/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\ProductOptionGroup::class);
                Route::get('/product-option-groups', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\ProductOptionGroup::class);
                Route::post('/product-option-groups', FluxErp\Actions\ProductOptionGroup\CreateProductOptionGroup::class);
                Route::put('/product-option-groups', FluxErp\Actions\ProductOptionGroup\UpdateProductOptionGroup::class);
                Route::delete('/product-option-groups/{id}', FluxErp\Actions\ProductOptionGroup\DeleteProductOptionGroup::class);

                // ProductProperties
                Route::get('/product-properties/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\ProductProperty::class);
                Route::get('/product-properties/', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\ProductProperty::class);
                Route::post('/product-properties/', FluxErp\Actions\ProductProperty\CreateProductProperty::class);
                Route::put('/product-properties/', FluxErp\Actions\ProductProperty\UpdateProductProperty::class);
                Route::delete('/product-properties/{id}', FluxErp\Actions\ProductProperty\DeleteProductProperty::class);

                // Projects
                Route::get('/projects/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Project::class);
                Route::get('/projects', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Project::class);
                Route::post('/projects', FluxErp\Actions\Project\CreateProject::class);
                Route::put('/projects', FluxErp\Actions\Project\UpdateProject::class);
                Route::delete('/projects/{id}', FluxErp\Actions\Project\DeleteProject::class);
                Route::post('/projects/finish', FluxErp\Actions\Project\FinishProject::class);

                // PurchaseInvoices
                Route::get('/purchase-invoices/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\PurchaseInvoice::class);
                Route::get('/purchase-invoices', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\PurchaseInvoice::class);
                Route::post('/purchase-invoices', FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice::class);
                Route::put('/purchase-invoices', FluxErp\Actions\PurchaseInvoice\UpdatePurchaseInvoice::class);
                Route::delete('/purchase-invoices/{id}', FluxErp\Actions\PurchaseInvoice\DeletePurchaseInvoice::class);
                Route::post('/purchase-invoices/finish', FluxErp\Actions\PurchaseInvoice\CreateOrderFromPurchaseInvoice::class);

                // PurchaseInvoicePositions
                Route::get('/purchase-invoice-positions/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\PurchaseInvoicePosition::class);
                Route::get('/purchase-invoice-positions', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\PurchaseInvoicePosition::class);
                Route::post('/purchase-invoice-positions', CreatePurchaseInvoicePosition::class);
                Route::put('/purchase-invoice-positions', UpdatePurchaseInvoicePosition::class);
                Route::delete('/purchase-invoice-positions/{id}', DeletePurchaseInvoicePosition::class);

                // Roles
                Route::get('/roles', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Role::class);
                Route::get('/roles/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Role::class);
                Route::get('/roles/user/{id}', [RoleController::class, 'showUserRoles']);
                Route::post('/roles', CreateRole::class);
                Route::put('/roles', UpdateRole::class);
                Route::put('/roles/give', UpdateRolePermissions::class)->defaults('give', true);
                Route::put('/roles/revoke', UpdateRolePermissions::class)->defaults('give', false);
                Route::put('/roles/users/assign', UpdateRoleUsers::class)->defaults('assign', true);
                Route::put('/roles/users/revoke', UpdateRoleUsers::class)->defaults('assign', false);
                Route::put('/roles/users/sync', UpdateUserRoles::class)->defaults('sync', true);
                Route::delete('/roles/{id}', DeleteRole::class);

                // SepaMandates
                Route::get('/sepa-mandates/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\SepaMandate::class);
                Route::get('/sepa-mandates', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\SepaMandate::class);
                Route::post('/sepa-mandates', CreateSepaMandate::class);
                Route::put('/sepa-mandates', UpdateSepaMandate::class);
                Route::delete('/sepa-mandates/{id}', DeleteSepaMandate::class);

                // SerialNumberRanges
                Route::get('/serial-number-ranges/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\SerialNumberRange::class);
                Route::get('/serial-number-ranges', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\SerialNumberRange::class);
                Route::post('/serial-number-ranges', CreateSerialNumberRange::class);
                Route::put('/serial-number-ranges', UpdateSerialNumberRange::class);
                Route::delete('/serial-number-ranges/{id}', DeleteSerialNumberRange::class);

                // SerialNumbers
                Route::get('/serial-numbers/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\SerialNumber::class);
                Route::get('/serial-numbers', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\SerialNumber::class);
                Route::post('/serial-numbers', CreateSerialNumber::class);
                Route::put('/serial-numbers', UpdateSerialNumber::class);
                Route::delete('/serial-numbers/{id}', DeleteSerialNumber::class);

                // Settings
                Route::get('/settings', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Setting::class);
                Route::post('/settings', CreateSetting::class);
                Route::put('/settings', UpdateSetting::class);

                // Subscriptions
                Route::get('/event-subscriptions', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\EventSubscription::class);
                Route::get('/event-subscriptions/user', [EventSubscriptionController::class, 'getUserSubscriptions']);
                Route::post('/event-subscriptions', CreateEventSubscription::class);
                Route::put('/event-subscriptions', UpdateEventSubscription::class);
                Route::delete('/event-subscriptions/{id}', DeleteEventSubscription::class);

                // StockPostings
                Route::get('/stock-postings/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\StockPosting::class);
                Route::get('/stock-postings', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\StockPosting::class);
                Route::post('/stock-postings', CreateStockPosting::class);
                Route::delete('/stock-postings/{id}', DeleteStockPosting::class);

                // Tag
                Route::get('/tags/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Tag::class);
                Route::get('/tags', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Tag::class);
                Route::post('/tags', CreateTag::class);
                Route::put('/tags', UpdateTag::class);
                Route::delete('/tags/{id}', DeleteTag::class);

                // Tasks
                Route::get('/tasks/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Task::class);
                Route::get('/tasks', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Task::class);
                Route::post('/tasks', CreateTask::class);
                Route::put('/tasks', UpdateTask::class);
                Route::delete('/tasks/{id}', DeleteTask::class);
                Route::post('/tasks/finish', FluxErp\Actions\Task\FinishTask::class);

                // Tickets
                Route::post('/tickets/toggle/', ToggleTicketUser::class);
                Route::get('/tickets/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Ticket::class);
                Route::get('/tickets', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Ticket::class);
                Route::post('/tickets', CreateTicket::class);
                Route::put('/tickets', UpdateTicket::class);
                Route::delete('/tickets/{id}', DeleteTicket::class);

                // TicketTypes
                Route::get('/ticket-types/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\TicketType::class);
                Route::get('/ticket-types', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\TicketType::class);
                Route::post('/ticket-types', CreateTicketType::class);
                Route::put('/ticket-types', UpdateTicketType::class);
                Route::delete('/ticket-types/{id}', DeleteTicketType::class);

                // TimeTracking
                Route::get('/user/work-time', [FluxErp\Http\Controllers\WorkTimeController::class, 'userIndex']);
                Route::get('/work-time', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\WorkTime::class);
                Route::post('/work-time', CreateWorkTime::class);
                Route::put('/work-time', UpdateWorkTime::class);
                Route::delete('/work-time/{id}', DeleteWorkTime::class);

                // TimeTrackingTypes
                Route::get('/time-tracking-types', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\WorkTimeType::class);
                Route::post('/time-tracking-types', CreateWorkTimeType::class);
                Route::put('/time-tracking-types', UpdateWorkTimeType::class);
                Route::delete('/time-tracking-types/{id}', DeleteWorkTimeType::class);

                // Translations
                Route::get('/language-line', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\LanguageLine::class);
                Route::post('/language-line', CreateLanguageLine::class);
                Route::put('/language-line', UpdateLanguageLine::class);
                Route::delete('/language-line/{id}', DeleteLanguageLine::class);

                // Units
                Route::get('/units/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Unit::class);
                Route::get('/units', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Unit::class);
                Route::post('/units', CreateUnit::class);
                Route::delete('/units/{id}', DeleteUnit::class);

                // Users
                Route::get('/user/settings', [SettingController::class, 'getUserSettings']);
                Route::get('/user', function (Request $request) {
                    $user = $request->user();
                    $user->permissions = $request->user()->permissions;

                    return ResponseHelper::createResponseFromBase(statusCode: 200, data: $user);
                });

                Route::get('/users/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\User::class);
                Route::get('/users', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\User::class);
                Route::post('/users', CreateUser::class);
                Route::put('/users', UpdateUser::class);
                Route::delete('/users/{id}', DeleteUser::class);

                // ValueLists
                Route::get('/value-lists/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\AdditionalColumn::class);
                Route::get('/value-lists', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\AdditionalColumn::class);
                Route::post('/value-lists', CreateValueList::class);
                Route::put('/value-lists', UpdateValueList::class);
                Route::delete('/value-lists/{id}', DeleteValueList::class);

                // VatRates
                Route::get('/vat-rates/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\VatRate::class);
                Route::get('/vat-rates', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\VatRate::class);
                Route::post('/vat-rates', CreateVatRate::class);
                Route::put('/vat-rates', UpdateVatRate::class);
                Route::delete('/vat-rates/{id}', DeleteVatRate::class);

                // Warehouses
                Route::get('/warehouses/{id}', [BaseController::class, 'show'])->defaults('model', FluxErp\Models\Warehouse::class);
                Route::get('/warehouses', [BaseController::class, 'index'])->defaults('model', FluxErp\Models\Warehouse::class);
                Route::post('/warehouses', CreateWarehouse::class);
                Route::put('/warehouses', UpdateWarehouse::class);
                Route::delete('/warehouses/{id}', DeleteWarehouse::class);
            });

        Broadcast::routes(['middleware' => ['auth:sanctum']]);
    });
