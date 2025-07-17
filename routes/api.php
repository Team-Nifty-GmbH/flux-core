<?php

use FluxErp\Actions\AdditionalColumn\CreateAdditionalColumn;
use FluxErp\Actions\AdditionalColumn\CreateValueList;
use FluxErp\Actions\AdditionalColumn\DeleteAdditionalColumn;
use FluxErp\Actions\AdditionalColumn\DeleteValueList;
use FluxErp\Actions\AdditionalColumn\UpdateAdditionalColumn;
use FluxErp\Actions\AdditionalColumn\UpdateValueList;
use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Address\DeleteAddress;
use FluxErp\Actions\Address\GenerateAddressLoginToken;
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
use FluxErp\Actions\Cart\CreateCart;
use FluxErp\Actions\Cart\DeleteCart;
use FluxErp\Actions\Cart\UpdateCart;
use FluxErp\Actions\Category\CreateCategory;
use FluxErp\Actions\Category\DeleteCategory;
use FluxErp\Actions\Category\UpdateCategory;
use FluxErp\Actions\Client\CreateClient;
use FluxErp\Actions\Client\DeleteClient;
use FluxErp\Actions\Client\UpdateClient;
use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Actions\Comment\DeleteComment;
use FluxErp\Actions\Comment\UpdateComment;
use FluxErp\Actions\Commission\CreateCommission;
use FluxErp\Actions\Commission\DeleteCommission;
use FluxErp\Actions\Commission\UpdateCommission;
use FluxErp\Actions\Communication\CreateCommunication;
use FluxErp\Actions\Communication\DeleteCommunication;
use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Contact\DeleteContact;
use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Actions\ContactBankConnection\CreateContactBankConnection;
use FluxErp\Actions\ContactBankConnection\DeleteContactBankConnection;
use FluxErp\Actions\ContactBankConnection\UpdateContactBankConnection;
use FluxErp\Actions\ContactOption\CreateContactOption;
use FluxErp\Actions\ContactOption\DeleteContactOption;
use FluxErp\Actions\ContactOption\UpdateContactOption;
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
use FluxErp\Actions\Industry\CreateIndustry;
use FluxErp\Actions\Industry\DeleteIndustry;
use FluxErp\Actions\Industry\UpdateIndustry;
use FluxErp\Actions\Language\CreateLanguage;
use FluxErp\Actions\Language\DeleteLanguage;
use FluxErp\Actions\Language\UpdateLanguage;
use FluxErp\Actions\LanguageLine\CreateLanguageLine;
use FluxErp\Actions\LanguageLine\DeleteLanguageLine;
use FluxErp\Actions\LanguageLine\UpdateLanguageLine;
use FluxErp\Actions\LeadLossReason\CreateLeadLossReason;
use FluxErp\Actions\LeadLossReason\DeleteLeadLossReason;
use FluxErp\Actions\LeadLossReason\UpdateLeadLossReason;
use FluxErp\Actions\LeadState\CreateLeadState;
use FluxErp\Actions\LeadState\DeleteLeadState;
use FluxErp\Actions\LeadState\UpdateLeadState;
use FluxErp\Actions\LedgerAccount\CreateLedgerAccount;
use FluxErp\Actions\LedgerAccount\DeleteLedgerAccount;
use FluxErp\Actions\LedgerAccount\UpdateLedgerAccount;
use FluxErp\Actions\MailAccount\CreateMailAccount;
use FluxErp\Actions\MailAccount\DeleteMailAccount;
use FluxErp\Actions\MailAccount\UpdateMailAccount;
use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\DeleteMediaCollection;
use FluxErp\Actions\Media\DownloadMedia;
use FluxErp\Actions\Media\DownloadMultipleMedia;
use FluxErp\Actions\Media\ReplaceMedia;
use FluxErp\Actions\Media\UpdateMedia;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Actions\NotificationSetting\UpdateNotificationSetting;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\Order\ToggleLock;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\OrderPosition\DeleteOrderPosition;
use FluxErp\Actions\OrderPosition\FillOrderPositions;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Actions\OrderType\CreateOrderType;
use FluxErp\Actions\OrderType\DeleteOrderType;
use FluxErp\Actions\OrderType\UpdateOrderType;
use FluxErp\Actions\PaymentReminder\CreatePaymentReminder;
use FluxErp\Actions\PaymentReminder\DeletePaymentReminder;
use FluxErp\Actions\PaymentReminder\UpdatePaymentReminder;
use FluxErp\Actions\PaymentReminderText\CreatePaymentReminderText;
use FluxErp\Actions\PaymentReminderText\DeletePaymentReminderText;
use FluxErp\Actions\PaymentReminderText\UpdatePaymentReminderText;
use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Actions\PaymentRun\DeletePaymentRun;
use FluxErp\Actions\PaymentRun\UpdatePaymentRun;
use FluxErp\Actions\PaymentType\CreatePaymentType;
use FluxErp\Actions\PaymentType\DeletePaymentType;
use FluxErp\Actions\PaymentType\UpdatePaymentType;
use FluxErp\Actions\Permission\CreatePermission;
use FluxErp\Actions\Permission\DeletePermission;
use FluxErp\Actions\Permission\UpdateUserPermissions;
use FluxErp\Actions\Price\CreatePrice;
use FluxErp\Actions\Price\DeletePrice;
use FluxErp\Actions\Price\UpdatePrice;
use FluxErp\Actions\PriceList\CreatePriceList;
use FluxErp\Actions\PriceList\DeletePriceList;
use FluxErp\Actions\PriceList\UpdatePriceList;
use FluxErp\Actions\Printer\CreatePrinter;
use FluxErp\Actions\Printer\DeletePrinter;
use FluxErp\Actions\Printer\UpdatePrinter;
use FluxErp\Actions\Printing;
use FluxErp\Actions\PrintJob\CreatePrintJob;
use FluxErp\Actions\PrintJob\DeletePrintJob;
use FluxErp\Actions\PrintJob\UpdatePrintJob;
use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Actions\Product\DeleteProduct;
use FluxErp\Actions\Product\ProductBundleProduct\CreateProductBundleProduct;
use FluxErp\Actions\Product\ProductBundleProduct\DeleteProductBundleProduct;
use FluxErp\Actions\Product\ProductBundleProduct\UpdateProductBundleProduct;
use FluxErp\Actions\Product\UpdateProduct;
use FluxErp\Actions\ProductCrossSelling\CreateProductCrossSelling;
use FluxErp\Actions\ProductCrossSelling\DeleteProductCrossSelling;
use FluxErp\Actions\ProductCrossSelling\UpdateProductCrossSelling;
use FluxErp\Actions\ProductOption\CreateProductOption;
use FluxErp\Actions\ProductOption\DeleteProductOption;
use FluxErp\Actions\ProductOption\UpdateProductOption;
use FluxErp\Actions\ProductOptionGroup\CreateProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\DeleteProductOptionGroup;
use FluxErp\Actions\ProductOptionGroup\UpdateProductOptionGroup;
use FluxErp\Actions\ProductProperty\CreateProductProperty;
use FluxErp\Actions\ProductProperty\DeleteProductProperty;
use FluxErp\Actions\ProductProperty\UpdateProductProperty;
use FluxErp\Actions\Project\CreateProject;
use FluxErp\Actions\Project\DeleteProject;
use FluxErp\Actions\Project\FinishProject;
use FluxErp\Actions\Project\UpdateProject;
use FluxErp\Actions\PurchaseInvoice\CreateOrderFromPurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\CreatePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\DeletePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoice\UpdatePurchaseInvoice;
use FluxErp\Actions\PurchaseInvoicePosition\CreatePurchaseInvoicePosition;
use FluxErp\Actions\PurchaseInvoicePosition\DeletePurchaseInvoicePosition;
use FluxErp\Actions\PurchaseInvoicePosition\UpdatePurchaseInvoicePosition;
use FluxErp\Actions\RecordOrigin\CreateRecordOrigin;
use FluxErp\Actions\RecordOrigin\DeleteRecordOrigin;
use FluxErp\Actions\RecordOrigin\UpdateRecordOrigin;
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
use FluxErp\Actions\Task\FinishTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Actions\Ticket\DeleteTicket;
use FluxErp\Actions\Ticket\ToggleTicketUser;
use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Actions\TicketType\CreateTicketType;
use FluxErp\Actions\TicketType\DeleteTicketType;
use FluxErp\Actions\TicketType\UpdateTicketType;
use FluxErp\Actions\Token\CreateToken;
use FluxErp\Actions\Token\DeleteToken;
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
use FluxErp\Http\Controllers\CommentController;
use FluxErp\Http\Controllers\EventSubscriptionController;
use FluxErp\Http\Controllers\LockController;
use FluxErp\Http\Controllers\PermissionController;
use FluxErp\Http\Controllers\PrintController;
use FluxErp\Http\Controllers\RoleController;
use FluxErp\Http\Controllers\SettingController;
use FluxErp\Http\Middleware\SetAcceptHeaders;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\Cart;
use FluxErp\Models\Category;
use FluxErp\Models\Client;
use FluxErp\Models\Comment;
use FluxErp\Models\Commission;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\ContactOption;
use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Models\Currency;
use FluxErp\Models\Discount;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Models\Industry;
use FluxErp\Models\Language;
use FluxErp\Models\LanguageLine;
use FluxErp\Models\LeadLossReason;
use FluxErp\Models\LeadState;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Lock;
use FluxErp\Models\MailAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Printer;
use FluxErp\Models\PrintJob;
use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\Project;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\RecordOrigin;
use FluxErp\Models\Role;
use FluxErp\Models\SepaMandate;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Models\Setting;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\Token;
use FluxErp\Models\Unit;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
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
        Route::post('/auth/token', [AuthController::class, 'authenticate']);

        Route::middleware(['auth:sanctum,token', 'ability:user,interface', 'localization', 'permission', 'api'])
            ->name('api.')
            ->group(callback: function (): void {
                // Validate Token
                Route::get('/auth/token/validate', [AuthController::class, 'validateToken']);
                Route::post('/logout', [AuthController::class, 'logout']);

                // AdditionalColumns
                Route::get('/additional-columns/{id}', [BaseController::class, 'show'])
                    ->defaults('model', AdditionalColumn::class);
                Route::get('/additional-columns', [BaseController::class, 'index'])
                    ->defaults('model', AdditionalColumn::class);
                Route::post('/additional-columns', CreateAdditionalColumn::class);
                Route::put('/additional-columns', UpdateAdditionalColumn::class);
                Route::delete('/additional-columns/{id}', DeleteAdditionalColumn::class);

                // Addresses
                Route::get('/addresses/{id}', [BaseController::class, 'show'])->defaults('model', Address::class);
                Route::get('/addresses', [BaseController::class, 'index'])->defaults('model', Address::class);
                Route::post('/addresses', CreateAddress::class);
                Route::put('/addresses', UpdateAddress::class);
                Route::delete('/addresses/{id}', DeleteAddress::class);
                Route::post('/addresses/{id}/login-token', GenerateAddressLoginToken::class);

                // AddressTypes
                Route::get('/address-types/{id}', [BaseController::class, 'show'])
                    ->defaults('model', AddressType::class);
                Route::get('/address-types', [BaseController::class, 'index'])
                    ->defaults('model', AddressType::class);
                Route::post('/address-types', CreateAddressType::class);
                Route::put('/address-types', UpdateAddressType::class);
                Route::delete('/address-types/{id}', DeleteAddressType::class);

                // BankConnections
                Route::get('/bank-connections/{id}', [BaseController::class, 'show'])
                    ->defaults('model', BankConnection::class);
                Route::get('/bank-connections', [BaseController::class, 'index'])
                    ->defaults('model', BankConnection::class);
                Route::post('/bank-connections', CreateBankConnection::class);
                Route::put('/bank-connections', UpdateBankConnection::class);
                Route::delete('/bank-connections/{id}', DeleteBankConnection::class);

                // Calendars
                Route::get('/calendars/{id}', [BaseController::class, 'show'])->defaults('model', Calendar::class);
                Route::get('/calendars', [BaseController::class, 'index'])->defaults('model', Calendar::class);
                Route::post('/calendars', CreateCalendar::class);
                Route::put('/calendars', UpdateCalendar::class);
                Route::delete('/calendars/{id}', DeleteCalendar::class);

                // CalendarEvents
                Route::get('/calendar-events/{id}', [BaseController::class, 'show'])
                    ->defaults('model', CalendarEvent::class);
                Route::get('/calendar-events', [BaseController::class, 'index'])
                    ->defaults('model', CalendarEvent::class);
                Route::post('/calendar-events', CreateCalendarEvent::class);
                Route::put('/calendar-events', UpdateCalendarEvent::class);
                Route::delete('/calendar-events/{id}', DeleteCalendarEvent::class);

                // Carts
                Route::get('/carts/{id}', [BaseController::class, 'show'])
                    ->defaults('model', Cart::class);
                Route::get('/carts', [BaseController::class, 'index'])
                    ->defaults('model', Cart::class);
                Route::post('/carts', CreateCart::class);
                Route::put('/carts', UpdateCart::class);
                Route::delete('/carts/{id}', DeleteCart::class);

                // Categories
                Route::get('/categories/{id}', [BaseController::class, 'show'])->defaults('model', Category::class);
                Route::get('/categories', [BaseController::class, 'index'])->defaults('model', Category::class);
                Route::post('/categories', CreateCategory::class);
                Route::put('/categories', UpdateCategory::class);
                Route::delete('/categories/{id}', DeleteCategory::class);

                // Clients
                Route::get('/clients/{id}', [BaseController::class, 'show'])->defaults('model', Client::class);
                Route::get('/clients', [BaseController::class, 'index'])->defaults('model', Client::class);
                Route::post('/clients', CreateClient::class);
                Route::put('/clients', UpdateClient::class);
                Route::delete('/clients/{id}', DeleteClient::class);

                // Comments
                Route::get('/{modelType}/comments/{id}', [CommentController::class, 'show'])
                    ->defaults('model', Comment::class);
                Route::post('/comments', CreateComment::class);
                Route::put('/comments', UpdateComment::class);
                Route::delete('/comments/{id}', DeleteComment::class);

                // Communications
                Route::get('/communications/{id}', [BaseController::class, 'show'])
                    ->defaults('model', Communication::class);
                Route::get('/communications', [BaseController::class, 'index'])
                    ->defaults('model', Communication::class);
                Route::post('/communications', CreateCommunication::class);
                Route::put('/communications', UpdateCommunication::class);
                Route::delete('/communications/{id}', DeleteCommunication::class);

                // Commissions
                Route::get('/commissions/{id}', [BaseController::class, 'show'])
                    ->defaults('model', Commission::class);
                Route::get('/commissions', [BaseController::class, 'index'])
                    ->defaults('model', Commission::class);
                Route::post('/commissions', CreateCommission::class);
                Route::put('/commissions', UpdateCommission::class);
                Route::delete('/commissions/{id}', DeleteCommission::class);

                // ContactBankConnections
                Route::get('/contact-bank-connections/{id}', [BaseController::class, 'show'])
                    ->defaults('model', ContactBankConnection::class);
                Route::get('/contact-bank-connections', [BaseController::class, 'index'])
                    ->defaults('model', ContactBankConnection::class);
                Route::post('/contact-bank-connections', CreateContactBankConnection::class);
                Route::put('/contact-bank-connections', UpdateContactBankConnection::class);
                Route::delete('/contact-bank-connections/{id}', DeleteContactBankConnection::class);

                // ContactOptions
                Route::get('/contact-options/{id}', [BaseController::class, 'show'])
                    ->defaults('model', ContactOption::class);
                Route::get('/contact-options', [BaseController::class, 'index'])
                    ->defaults('model', ContactOption::class);
                Route::post('/contact-options', CreateContactOption::class);
                Route::put('/contact-options', UpdateContactOption::class);
                Route::delete('/contact-options', DeleteContactOption::class);

                // Industries
                Route::get('/industries/{id}', [BaseController::class, 'show'])
                    ->defaults('model', Industry::class);
                Route::get('/industries', [BaseController::class, 'index'])
                    ->defaults('model', Industry::class);
                Route::post('/industries', CreateIndustry::class);
                Route::put('/industries', UpdateIndustry::class);
                Route::delete('/industries/{id}', DeleteIndustry::class);

                // RecordOrigins
                Route::get('/record-origins/{id}', [BaseController::class, 'show'])
                    ->defaults('model', RecordOrigin::class);
                Route::get('/record-origins', [BaseController::class, 'index'])
                    ->defaults('model', RecordOrigin::class);
                Route::post('/record-origins', CreateRecordOrigin::class);
                Route::put('/record-origins', UpdateRecordOrigin::class);
                Route::delete('/record-origins/{id}', DeleteRecordOrigin::class);

                // Contacts
                Route::get('/contacts/{id}', [BaseController::class, 'show'])->defaults('model', Contact::class);
                Route::get('/contacts', [BaseController::class, 'index'])->defaults('model', Contact::class);
                Route::post('/contacts', CreateContact::class);
                Route::put('/contacts', UpdateContact::class);
                Route::delete('/contacts/{id}', DeleteContact::class);

                // Countries
                Route::get('/countries/{id}', [BaseController::class, 'show'])->defaults('model', Country::class);
                Route::get('/countries', [BaseController::class, 'index'])->defaults('model', Country::class);
                Route::post('/countries', CreateCountry::class);
                Route::put('/countries', UpdateCountry::class);
                Route::delete('/countries/{id}', DeleteCountry::class);

                // CountryRegions
                Route::get('/country-regions/{id}', [BaseController::class, 'show'])
                    ->defaults('model', CountryRegion::class);
                Route::get('/country-regions', [BaseController::class, 'index'])
                    ->defaults('model', CountryRegion::class);
                Route::post('/country-regions', CreateCountryRegion::class);
                Route::put('/country-regions', UpdateCountryRegion::class);
                Route::delete('/country-regions/{id}', DeleteCountryRegion::class);

                // Currencies
                Route::get('/currencies/{id}', [BaseController::class, 'show'])->defaults('model', Currency::class);
                Route::get('/currencies', [BaseController::class, 'index'])->defaults('model', Currency::class);
                Route::post('/currencies', CreateCurrency::class);
                Route::put('/currencies', UpdateCurrency::class);
                Route::delete('/currencies/{id}', DeleteCurrency::class);

                // Discounts
                Route::get('/discounts/{id}', [BaseController::class, 'show'])->defaults('model', Discount::class);
                Route::get('/discounts', [BaseController::class, 'index'])->defaults('model', Discount::class);
                Route::post('/discounts', CreateDiscount::class);
                Route::put('/discounts', UpdateDiscount::class);
                Route::delete('/discounts/{id}', DeleteDiscount::class);

                // Events
                Route::get('/events', [EventSubscriptionController::class, 'getEvents']);

                // FormBuilderForm
                Route::get('/form-builder/forms/{id}', [BaseController::class, 'show'])
                    ->defaults('model', FormBuilderForm::class);
                Route::get('/form-builder/forms', [BaseController::class, 'index'])
                    ->defaults('model', FormBuilderForm::class);
                Route::post('/form-builder/forms', CreateFormBuilderForm::class);
                Route::put('/form-builder/forms', UpdateFormBuilderForm::class);
                Route::delete('/form-builder/forms/{id}', DeleteFormBuilderForm::class);

                // FormBuilderSection
                Route::get('/form-builder/sections/{id}', [BaseController::class, 'show'])
                    ->defaults('model', FormBuilderSection::class);
                Route::get('/form-builder/sections', [BaseController::class, 'index'])
                    ->defaults('model', FormBuilderSection::class);
                Route::post('/form-builder/sections', CreateFormBuilderSection::class);
                Route::put('/form-builder/sections', UpdateFormBuilderSection::class);
                Route::delete('/form-builder/sections/{id}', DeleteFormBuilderSection::class);

                // FormBuilderField
                Route::get('/form-builder/fields/{id}', [BaseController::class, 'show'])
                    ->defaults('model', FormBuilderField::class);
                Route::get('/form-builder/fields', [BaseController::class, 'index'])
                    ->defaults('model', FormBuilderField::class);
                Route::post('/form-builder/fields', CreateFormBuilderField::class);
                Route::put('/form-builder/fields', UpdateFormBuilderField::class);
                Route::delete('/form-builder/fields/{id}', DeleteFormBuilderField::class);

                // FormBuilderResponse
                Route::get('/form-builder/responses/{id}', [BaseController::class, 'show'])
                    ->defaults('model', FormBuilderResponse::class);
                Route::get('/form-builder/responses', [BaseController::class, 'index'])
                    ->defaults('model', FormBuilderResponse::class);
                Route::post('/form-builder/responses', CreateFormBuilderResponse::class);
                Route::delete('/form-builder/responses/{id}', DeleteFormBuilderResponse::class);

                // FormBuilderFieldsResponse
                Route::get('/form-builder/fields-responses/{id}', [BaseController::class, 'show'])
                    ->defaults('model', FormBuilderFieldResponse::class);
                Route::get('/form-builder/fields-responses', [BaseController::class, 'index'])
                    ->defaults('model', FormBuilderFieldResponse::class);
                Route::post('/form-builder/fields-responses', CreateFormBuilderFieldResponse::class);
                Route::put('/form-builder/fields-responses', UpdateFormBuilderFieldResponse::class);
                Route::delete('/form-builder/fields-responses/{id}', DeleteFormBuilderFieldResponse::class);

                // Languages
                Route::get('/languages/{id}', [BaseController::class, 'show'])->defaults('model', Language::class);
                Route::get('/languages', [BaseController::class, 'index'])->defaults('model', Language::class);
                Route::post('/languages', CreateLanguage::class);
                Route::put('/languages', UpdateLanguage::class);
                Route::delete('/languages/{id}', DeleteLanguage::class);

                // LeadLossReasons
                Route::get('/lead-loss-reasons/{id}', [BaseController::class, 'show'])
                    ->defaults('model', LeadLossReason::class);
                Route::get('/lead-loss-reasons', [BaseController::class, 'index'])
                    ->defaults('model', LeadLossReason::class);
                Route::post('/lead-loss-reasons', CreateLeadLossReason::class);
                Route::put('/lead-loss-reasons', UpdateLeadLossReason::class);
                Route::delete('/lead-loss-reasons/{id}', DeleteLeadLossReason::class);

                // LeadStates
                Route::get('/lead-states/{id}', [BaseController::class, 'show'])
                    ->defaults('model', LeadState::class);
                Route::get('/lead-states', [BaseController::class, 'index'])
                    ->defaults('model', LeadState::class);
                Route::post('/lead-states', CreateLeadState::class);
                Route::put('/lead-states', UpdateLeadState::class);
                Route::delete('/lead-states/{id}', DeleteLeadState::class);

                // LedgerAccounts
                Route::get('/ledger-accounts/{id}', [BaseController::class, 'show'])
                    ->defaults('model', LedgerAccount::class);
                Route::get('/ledger-accounts', [BaseController::class, 'index'])
                    ->defaults('model', LedgerAccount::class);
                Route::post('/ledger-accounts', CreateLedgerAccount::class);
                Route::put('/ledger-accounts', UpdateLedgerAccount::class);
                Route::delete('/ledger-accounts/{id}', DeleteLedgerAccount::class);

                // Locking
                Route::get('/user/locks', [LockController::class, 'showUserLocks']);
                Route::get('/locks', [BaseController::class, 'index'])->defaults('model', Lock::class);
                Route::get('/{modelType}/lock', [LockController::class, 'lock']);

                // MailAccounts
                Route::get('/mail-accounts/{id}', [BaseController::class, 'show'])
                    ->defaults('model', MailAccount::class);
                Route::get('/mail-accounts', [BaseController::class, 'index'])->defaults('model', MailAccount::class);
                Route::post('/mail-accounts', CreateMailAccount::class);
                Route::put('/mail-accounts', UpdateMailAccount::class);
                Route::delete('/mail-accounts/{id}', DeleteMailAccount::class);

                // Media
                Route::get('/media/private/{id}', DownloadMedia::class)
                    ->withoutMiddleware(SetAcceptHeaders::class);
                Route::get('/media/download-multiple', DownloadMultipleMedia::class);
                Route::post('/media/{id}', ReplaceMedia::class);
                Route::post('/media', UploadMedia::class);
                Route::put('/media', UpdateMedia::class);
                Route::delete('/media/{id}', DeleteMedia::class);
                Route::delete('/media-collections', DeleteMediaCollection::class);

                // NotificationSettings
                Route::put('/notifications', UpdateNotificationSetting::class)
                    ->defaults('is_anonymous', true);
                Route::put('/user/notifications', UpdateNotificationSetting::class);

                // Orders
                Route::get('/orders/{id}', [BaseController::class, 'show'])->defaults('model', Order::class);
                Route::get('/orders', [BaseController::class, 'index'])->defaults('model', Order::class);
                Route::post('/orders', CreateOrder::class);
                Route::put('/orders', UpdateOrder::class);
                Route::put('/orders/{id}/toggle-lock', ToggleLock::class);
                Route::delete('/orders/{id}', DeleteOrder::class);

                // OrderPositions
                Route::get('/order-positions/{id}', [BaseController::class, 'show'])
                    ->defaults('model', OrderPosition::class);
                Route::get('/order-positions', [BaseController::class, 'index'])
                    ->defaults('model', OrderPosition::class);
                Route::post('/order-positions', CreateOrderPosition::class);
                Route::post('/order-positions/fill', FillOrderPositions::class);
                Route::put('/order-positions', UpdateOrderPosition::class);
                Route::delete('/order-positions/{id}', DeleteOrderPosition::class);

                // OrderTypes
                Route::get('/order-types/{id}', [BaseController::class, 'show'])->defaults('model', OrderType::class);
                Route::get('/order-types', [BaseController::class, 'index'])->defaults('model', OrderType::class);
                Route::post('/order-types', CreateOrderType::class);
                Route::put('/order-types', UpdateOrderType::class);
                Route::delete('/order-types/{id}', DeleteOrderType::class);

                // PaymentReminders
                Route::get('/payment-reminders/{id}', [BaseController::class, 'show'])
                    ->defaults('model', PaymentReminder::class);
                Route::get('/payment-reminders', [BaseController::class, 'index'])
                    ->defaults('model', PaymentReminder::class);
                Route::post('/payment-reminders', CreatePaymentReminder::class);
                Route::put('/payment-reminders', UpdatePaymentReminder::class);
                Route::delete('/payment-reminders/{id}', DeletePaymentReminder::class);

                // PaymentReminderTexts
                Route::get('/payment-reminder-texts/{id}', [BaseController::class, 'show'])
                    ->defaults('model', PaymentReminderText::class);
                Route::get('/payment-reminder-texts', [BaseController::class, 'index'])
                    ->defaults('model', PaymentReminderText::class);
                Route::post('/payment-reminder-texts', CreatePaymentReminderText::class);
                Route::put('/payment-reminder-texts', UpdatePaymentReminderText::class);
                Route::delete('/payment-reminder-texts/{id}', DeletePaymentReminderText::class);

                // PaymentRuns
                Route::get('/payment-runs/{id}', [BaseController::class, 'show'])->defaults('model', PaymentRun::class);
                Route::get('/payment-runs', [BaseController::class, 'index'])->defaults('model', PaymentRun::class);
                Route::post('/payment-runs', CreatePaymentRun::class);
                Route::put('/payment-runs', UpdatePaymentRun::class);
                Route::delete('/payment-runs/{id}', DeletePaymentRun::class);

                // PaymentTypes
                Route::get('/payment-types/{id}', [BaseController::class, 'show'])
                    ->defaults('model', PaymentType::class);
                Route::get('/payment-types', [BaseController::class, 'index'])->defaults('model', PaymentType::class);
                Route::post('/payment-types', CreatePaymentType::class);
                Route::put('/payment-types', UpdatePaymentType::class);
                Route::delete('/payment-types/{id}', DeletePaymentType::class);

                // Permissions
                Route::get('/permissions', [BaseController::class, 'index'])->defaults('model', Permission::class);
                Route::get('/permissions/user/{id}', [PermissionController::class, 'showUserPermissions']);
                Route::post('/permissions', CreatePermission::class);
                Route::put('/permissions/give', UpdateUserPermissions::class)->defaults('give', true);
                Route::put('/permissions/revoke', UpdateUserPermissions::class)->defaults('give', false);
                Route::put('/permissions/sync', UpdateUserPermissions::class)->defaults('sync', true);
                Route::delete('/permissions/{id}', DeletePermission::class);

                // Prices
                Route::get('/prices/{id}', [BaseController::class, 'show'])->defaults('model', Price::class);
                Route::get('/prices', [BaseController::class, 'index'])->defaults('model', Price::class);
                Route::post('/prices', CreatePrice::class);
                Route::put('/prices', UpdatePrice::class);
                Route::delete('/prices/{id}', DeletePrice::class);

                // PriceLists
                Route::get('/price-lists/{id}', [BaseController::class, 'show'])->defaults('model', PriceList::class);
                Route::get('/price-lists', [BaseController::class, 'index'])->defaults('model', PriceList::class);
                Route::post('/price-lists', CreatePriceList::class);
                Route::put('/price-lists', UpdatePriceList::class);
                Route::delete('/price-lists/{id}', DeletePriceList::class);

                // Printers
                Route::get('/printers/{id}', [BaseController::class, 'show'])->defaults('model', Printer::class);
                Route::get('/printers', [BaseController::class, 'index'])->defaults('model', Printer::class);
                Route::post('/printers', CreatePrinter::class);
                Route::put('/printers', UpdatePrinter::class);
                Route::delete('/printers/{id}', DeletePrinter::class);

                // PrintJobs
                Route::get('/print-jobs/{id}', [BaseController::class, 'show'])->defaults('model', PrintJob::class);
                Route::get('/print-jobs', [BaseController::class, 'index'])->defaults('model', PrintJob::class);
                Route::post('/print-jobs', CreatePrintJob::class);
                Route::put('/print-jobs', UpdatePrintJob::class);
                Route::delete('/print-jobs/{id}', DeletePrintJob::class);

                // PrintPdf
                Route::post('/print/views', [PrintController::class, 'getPrintViews']);
                Route::post('/print/render', Printing::class)
                    ->defaults('html', true)
                    ->defaults('preview', false);
                Route::post('/print/pdf', Printing::class)
                    ->defaults('html', false);

                // Products
                Route::get('/products/{id}', [BaseController::class, 'show'])->defaults('model', Product::class);
                Route::get('/products', [BaseController::class, 'index'])->defaults('model', Product::class);
                Route::post('/products', CreateProduct::class);
                Route::put('/products', UpdateProduct::class);
                Route::delete('/products/{id}', DeleteProduct::class);

                // Product bundle products
                Route::get('/product-bundle-products/{id}', [BaseController::class, 'show'])
                    ->defaults('model', ProductBundleProduct::class);
                Route::get('/product-bundle-products', [BaseController::class, 'index'])
                    ->defaults('model', ProductBundleProduct::class);
                Route::post('/product-bundle-products', CreateProductBundleProduct::class);
                Route::put('/product-bundle-products', UpdateProductBundleProduct::class);
                Route::delete('/product-bundle-products/{id}', DeleteProductBundleProduct::class);

                // ProductCrossSellings
                Route::get('/product-cross-sellings/{id}', [BaseController::class, 'show'])
                    ->defaults('model', ProductCrossSelling::class);
                Route::get('/product-cross-sellings', [BaseController::class, 'index'])
                    ->defaults('model', ProductCrossSelling::class);
                Route::post('/product-cross-sellings', CreateProductCrossSelling::class);
                Route::put('/product-cross-sellings', UpdateProductCrossSelling::class);
                Route::delete('/product-cross-sellings/{id}', DeleteProductCrossSelling::class);

                // ProductOptions
                Route::get('/product-options/{id}', [BaseController::class, 'show'])
                    ->defaults('model', ProductOption::class);
                Route::get('/product-options', [BaseController::class, 'index'])
                    ->defaults('model', ProductOption::class);
                Route::post('/product-options', CreateProductOption::class);
                Route::put('/product-options', UpdateProductOption::class);
                Route::delete('/product-options/{id}', DeleteProductOption::class);

                // ProductOptionGroups
                Route::get('/product-option-groups/{id}', [BaseController::class, 'show'])
                    ->defaults('model', ProductOptionGroup::class);
                Route::get('/product-option-groups', [BaseController::class, 'index'])
                    ->defaults('model', ProductOptionGroup::class);
                Route::post('/product-option-groups', CreateProductOptionGroup::class);
                Route::put('/product-option-groups', UpdateProductOptionGroup::class);
                Route::delete('/product-option-groups/{id}', DeleteProductOptionGroup::class);

                // ProductProperties
                Route::get('/product-properties/{id}', [BaseController::class, 'show'])
                    ->defaults('model', ProductProperty::class);
                Route::get('/product-properties', [BaseController::class, 'index'])
                    ->defaults('model', ProductProperty::class);
                Route::post('/product-properties', CreateProductProperty::class);
                Route::put('/product-properties', UpdateProductProperty::class);
                Route::delete('/product-properties/{id}', DeleteProductProperty::class);

                // Projects
                Route::get('/projects/{id}', [BaseController::class, 'show'])->defaults('model', Project::class);
                Route::get('/projects', [BaseController::class, 'index'])->defaults('model', Project::class);
                Route::post('/projects', CreateProject::class);
                Route::put('/projects', UpdateProject::class);
                Route::delete('/projects/{id}', DeleteProject::class);
                Route::post('/projects/finish', FinishProject::class);

                // PurchaseInvoices
                Route::get('/purchase-invoices/{id}', [BaseController::class, 'show'])
                    ->defaults('model', PurchaseInvoice::class);
                Route::get('/purchase-invoices', [BaseController::class, 'index'])
                    ->defaults('model', PurchaseInvoice::class);
                Route::post('/purchase-invoices', CreatePurchaseInvoice::class);
                Route::put('/purchase-invoices', UpdatePurchaseInvoice::class);
                Route::delete('/purchase-invoices/{id}', DeletePurchaseInvoice::class);
                Route::post('/purchase-invoices/finish', CreateOrderFromPurchaseInvoice::class);

                // PurchaseInvoicePositions
                Route::get('/purchase-invoice-positions/{id}', [BaseController::class, 'show'])
                    ->defaults('model', PurchaseInvoicePosition::class);
                Route::get('/purchase-invoice-positions', [BaseController::class, 'index'])
                    ->defaults('model', PurchaseInvoicePosition::class);
                Route::post('/purchase-invoice-positions', CreatePurchaseInvoicePosition::class);
                Route::put('/purchase-invoice-positions', UpdatePurchaseInvoicePosition::class);
                Route::delete('/purchase-invoice-positions/{id}', DeletePurchaseInvoicePosition::class);

                // Roles
                Route::get('/roles', [BaseController::class, 'index'])->defaults('model', Role::class);
                Route::get('/roles/{id}', [BaseController::class, 'show'])->defaults('model', Role::class);
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
                Route::get('/sepa-mandates/{id}', [BaseController::class, 'show'])
                    ->defaults('model', SepaMandate::class);
                Route::get('/sepa-mandates', [BaseController::class, 'index'])->defaults('model', SepaMandate::class);
                Route::post('/sepa-mandates', CreateSepaMandate::class);
                Route::put('/sepa-mandates', UpdateSepaMandate::class);
                Route::delete('/sepa-mandates/{id}', DeleteSepaMandate::class);

                // SerialNumberRanges
                Route::get('/serial-number-ranges/{id}', [BaseController::class, 'show'])
                    ->defaults('model', SerialNumberRange::class);
                Route::get('/serial-number-ranges', [BaseController::class, 'index'])
                    ->defaults('model', SerialNumberRange::class);
                Route::post('/serial-number-ranges', CreateSerialNumberRange::class);
                Route::put('/serial-number-ranges', UpdateSerialNumberRange::class);
                Route::delete('/serial-number-ranges/{id}', DeleteSerialNumberRange::class);

                // SerialNumbers
                Route::get('/serial-numbers/{id}', [BaseController::class, 'show'])
                    ->defaults('model', SerialNumber::class);
                Route::get('/serial-numbers', [BaseController::class, 'index'])->defaults('model', SerialNumber::class);
                Route::post('/serial-numbers', CreateSerialNumber::class);
                Route::put('/serial-numbers', UpdateSerialNumber::class);
                Route::delete('/serial-numbers/{id}', DeleteSerialNumber::class);

                // Settings
                Route::get('/settings', [BaseController::class, 'index'])->defaults('model', Setting::class);
                Route::post('/settings', CreateSetting::class);
                Route::put('/settings', UpdateSetting::class);

                // Subscriptions
                Route::get('/event-subscriptions', [BaseController::class, 'index'])
                    ->defaults('model', EventSubscription::class);
                Route::get('/event-subscriptions/user', [EventSubscriptionController::class, 'getUserSubscriptions']);
                Route::post('/event-subscriptions', CreateEventSubscription::class);
                Route::put('/event-subscriptions', UpdateEventSubscription::class);
                Route::delete('/event-subscriptions/{id}', DeleteEventSubscription::class);

                // StockPostings
                Route::get('/stock-postings/{id}', [BaseController::class, 'show'])
                    ->defaults('model', StockPosting::class);
                Route::get('/stock-postings', [BaseController::class, 'index'])->defaults('model', StockPosting::class);
                Route::post('/stock-postings', CreateStockPosting::class);
                Route::delete('/stock-postings/{id}', DeleteStockPosting::class);

                // Tag
                Route::get('/tags/{id}', [BaseController::class, 'show'])->defaults('model', Tag::class);
                Route::get('/tags', [BaseController::class, 'index'])->defaults('model', Tag::class);
                Route::post('/tags', CreateTag::class);
                Route::put('/tags', UpdateTag::class);
                Route::delete('/tags/{id}', DeleteTag::class);

                // Tokens
                Route::get('/tokens/{id}', [BaseController::class, 'show'])->defaults('model', Token::class);
                Route::get('/tokens', [BaseController::class, 'index'])->defaults('model', Token::class);
                Route::post('/tokens', CreateToken::class);
                Route::delete('/tokens/{id}', DeleteToken::class);

                // Tasks
                Route::get('/tasks/{id}', [BaseController::class, 'show'])->defaults('model', Task::class);
                Route::get('/tasks', [BaseController::class, 'index'])->defaults('model', Task::class);
                Route::post('/tasks', CreateTask::class);
                Route::put('/tasks', UpdateTask::class);
                Route::delete('/tasks/{id}', DeleteTask::class);
                Route::post('/tasks/finish', FinishTask::class);

                // Tickets
                Route::post('/tickets/toggle', ToggleTicketUser::class);
                Route::get('/tickets/{id}', [BaseController::class, 'show'])->defaults('model', Ticket::class);
                Route::get('/tickets', [BaseController::class, 'index'])->defaults('model', Ticket::class);
                Route::post('/tickets', CreateTicket::class);
                Route::put('/tickets', UpdateTicket::class);
                Route::delete('/tickets/{id}', DeleteTicket::class);

                // TicketTypes
                Route::get('/ticket-types/{id}', [BaseController::class, 'show'])->defaults('model', TicketType::class);
                Route::get('/ticket-types', [BaseController::class, 'index'])->defaults('model', TicketType::class);
                Route::post('/ticket-types', CreateTicketType::class);
                Route::put('/ticket-types', UpdateTicketType::class);
                Route::delete('/ticket-types/{id}', DeleteTicketType::class);

                // TimeTracking
                Route::get('/user/work-times', [FluxErp\Http\Controllers\WorkTimeController::class, 'userIndex']);
                Route::get('/work-times', [BaseController::class, 'index'])->defaults('model', WorkTime::class);
                Route::post('/work-times', CreateWorkTime::class);
                Route::put('/work-times', UpdateWorkTime::class);
                Route::delete('/work-times/{id}', DeleteWorkTime::class);

                // TimeTrackingTypes
                Route::get('/time-tracking-types', [BaseController::class, 'index'])
                    ->defaults('model', WorkTimeType::class);
                Route::post('/time-tracking-types', CreateWorkTimeType::class);
                Route::put('/time-tracking-types', UpdateWorkTimeType::class);
                Route::delete('/time-tracking-types/{id}', DeleteWorkTimeType::class);

                // Translations
                Route::get('/language-lines', [BaseController::class, 'index'])->defaults('model', LanguageLine::class);
                Route::post('/language-lines', CreateLanguageLine::class);
                Route::put('/language-lines', UpdateLanguageLine::class);
                Route::delete('/language-lines/{id}', DeleteLanguageLine::class);

                // Units
                Route::get('/units/{id}', [BaseController::class, 'show'])->defaults('model', Unit::class);
                Route::get('/units', [BaseController::class, 'index'])->defaults('model', Unit::class);
                Route::post('/units', CreateUnit::class);
                Route::delete('/units/{id}', DeleteUnit::class);

                // Users
                Route::get('/user/settings', [SettingController::class, 'getUserSettings']);
                Route::get('/user', function (Request $request) {
                    $user = $request->user();
                    $user->permissions = $request->user()->permissions;

                    return ResponseHelper::createResponseFromBase(statusCode: 200, data: $user);
                });

                Route::get('/users/{id}', [BaseController::class, 'show'])->defaults('model', User::class);
                Route::get('/users', [BaseController::class, 'index'])->defaults('model', User::class);
                Route::post('/users', CreateUser::class);
                Route::put('/users', UpdateUser::class);
                Route::delete('/users/{id}', DeleteUser::class);

                // ValueLists
                Route::post('/value-lists', CreateValueList::class);
                Route::put('/value-lists', UpdateValueList::class);
                Route::delete('/value-lists/{id}', DeleteValueList::class);

                // VatRates
                Route::get('/vat-rates/{id}', [BaseController::class, 'show'])->defaults('model', VatRate::class);
                Route::get('/vat-rates', [BaseController::class, 'index'])->defaults('model', VatRate::class);
                Route::post('/vat-rates', CreateVatRate::class);
                Route::put('/vat-rates', UpdateVatRate::class);
                Route::delete('/vat-rates/{id}', DeleteVatRate::class);

                // Warehouses
                Route::get('/warehouses/{id}', [BaseController::class, 'show'])->defaults('model', Warehouse::class);
                Route::get('/warehouses', [BaseController::class, 'index'])->defaults('model', Warehouse::class);
                Route::post('/warehouses', CreateWarehouse::class);
                Route::put('/warehouses', UpdateWarehouse::class);
                Route::delete('/warehouses/{id}', DeleteWarehouse::class);
            });

        Route::get('/media/{file_name}', DownloadMedia::class)->name('media.public');
        Broadcast::routes(['middleware' => ['auth:sanctum']]);
    });
