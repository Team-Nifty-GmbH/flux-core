<?php

use FluxErp\Actions\AbsencePolicy\CreateAbsencePolicy;
use FluxErp\Actions\AbsencePolicy\DeleteAbsencePolicy;
use FluxErp\Actions\AbsencePolicy\UpdateAbsencePolicy;
use FluxErp\Actions\AbsenceRequest\ApproveAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\CreateAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\DeleteAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\RejectAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\RevokeAbsenceRequest;
use FluxErp\Actions\AbsenceRequest\UpdateAbsenceRequest;
use FluxErp\Actions\AbsenceType\CreateAbsenceType;
use FluxErp\Actions\AbsenceType\DeleteAbsenceType;
use FluxErp\Actions\AbsenceType\UpdateAbsenceType;
use FluxErp\Actions\Address\CreateAddress;
use FluxErp\Actions\Address\DeleteAddress;
use FluxErp\Actions\Address\UpdateAddress;
use FluxErp\Actions\AddressType\CreateAddressType;
use FluxErp\Actions\AddressType\DeleteAddressType;
use FluxErp\Actions\AddressType\UpdateAddressType;
use FluxErp\Actions\AttributeTranslation\DeleteAttributeTranslation;
use FluxErp\Actions\AttributeTranslation\UpsertAttributeTranslation;
use FluxErp\Actions\BankConnection\CreateBankConnection;
use FluxErp\Actions\BankConnection\DeleteBankConnection;
use FluxErp\Actions\BankConnection\UpdateBankConnection;
use FluxErp\Actions\Calendar\CreateCalendar;
use FluxErp\Actions\Calendar\DeleteCalendar;
use FluxErp\Actions\Calendar\UpdateCalendar;
use FluxErp\Actions\CalendarEvent\CancelCalendarEvent;
use FluxErp\Actions\CalendarEvent\CreateCalendarEvent;
use FluxErp\Actions\CalendarEvent\DeleteCalendarEvent;
use FluxErp\Actions\CalendarEvent\ReactivateCalendarEvent;
use FluxErp\Actions\CalendarEvent\UpdateCalendarEvent;
use FluxErp\Actions\Cart\CreateCart;
use FluxErp\Actions\Cart\DeleteCart;
use FluxErp\Actions\Cart\UpdateCart;
use FluxErp\Actions\CartItem\CreateCartItem;
use FluxErp\Actions\CartItem\DeleteCartItem;
use FluxErp\Actions\CartItem\UpdateCartItem;
use FluxErp\Actions\Category\CreateCategory;
use FluxErp\Actions\Category\DeleteCategory;
use FluxErp\Actions\Category\UpdateCategory;
use FluxErp\Actions\Comment\CreateComment;
use FluxErp\Actions\Comment\DeleteComment;
use FluxErp\Actions\Comment\UpdateComment;
use FluxErp\Actions\Commission\CreateCommission;
use FluxErp\Actions\Commission\DeleteCommission;
use FluxErp\Actions\Commission\UpdateCommission;
use FluxErp\Actions\CommissionRate\CreateCommissionRate;
use FluxErp\Actions\CommissionRate\DeleteCommissionRate;
use FluxErp\Actions\CommissionRate\UpdateCommissionRate;
use FluxErp\Actions\Communication\CreateCommunication;
use FluxErp\Actions\Communication\DeleteCommunication;
use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Actions\Contact\CreateContact;
use FluxErp\Actions\Contact\DeleteContact;
use FluxErp\Actions\Contact\RestoreContact;
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
use FluxErp\Actions\DiscountGroup\CreateDiscountGroup;
use FluxErp\Actions\DiscountGroup\DeleteDiscountGroup;
use FluxErp\Actions\DiscountGroup\UpdateDiscountGroup;
use FluxErp\Actions\EmailTemplate\CreateEmailTemplate;
use FluxErp\Actions\EmailTemplate\DeleteEmailTemplate;
use FluxErp\Actions\EmailTemplate\UpdateEmailTemplate;
use FluxErp\Actions\Employee\AssignWorkTimeModel;
use FluxErp\Actions\Employee\CreateEmployee;
use FluxErp\Actions\Employee\DeleteEmployee;
use FluxErp\Actions\Employee\UpdateEmployee;
use FluxErp\Actions\EmployeeBalanceAdjustment\CreateEmployeeBalanceAdjustment;
use FluxErp\Actions\EmployeeBalanceAdjustment\DeleteEmployeeBalanceAdjustment;
use FluxErp\Actions\EmployeeBalanceAdjustment\UpdateEmployeeBalanceAdjustment;
use FluxErp\Actions\EmployeeDay\CloseEmployeeDay;
use FluxErp\Actions\EmployeeDay\CreateEmployeeDay;
use FluxErp\Actions\EmployeeDay\DeleteEmployeeDay;
use FluxErp\Actions\EmployeeDay\UpdateEmployeeDay;
use FluxErp\Actions\EmployeeDepartment\CreateEmployeeDepartment;
use FluxErp\Actions\EmployeeDepartment\DeleteEmployeeDepartment;
use FluxErp\Actions\EmployeeDepartment\UpdateEmployeeDepartment;
use FluxErp\Actions\EmployeeWorkTimeModel\CreateEmployeeWorkTimeModel;
use FluxErp\Actions\EmployeeWorkTimeModel\DeleteEmployeeWorkTimeModel;
use FluxErp\Actions\EmployeeWorkTimeModel\UpdateEmployeeWorkTimeModel;
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
use FluxErp\Actions\Holiday\CreateHoliday;
use FluxErp\Actions\Holiday\DeleteHoliday;
use FluxErp\Actions\Holiday\UpdateHoliday;
use FluxErp\Actions\Industry\CreateIndustry;
use FluxErp\Actions\Industry\DeleteIndustry;
use FluxErp\Actions\Industry\UpdateIndustry;
use FluxErp\Actions\Language\CreateLanguage;
use FluxErp\Actions\Language\DeleteLanguage;
use FluxErp\Actions\Language\UpdateLanguage;
use FluxErp\Actions\Lead\CreateLead;
use FluxErp\Actions\Lead\DeleteLead;
use FluxErp\Actions\Lead\UpdateLead;
use FluxErp\Actions\LeadLossReason\CreateLeadLossReason;
use FluxErp\Actions\LeadLossReason\DeleteLeadLossReason;
use FluxErp\Actions\LeadLossReason\UpdateLeadLossReason;
use FluxErp\Actions\LeadState\CreateLeadState;
use FluxErp\Actions\LeadState\DeleteLeadState;
use FluxErp\Actions\LeadState\UpdateLeadState;
use FluxErp\Actions\LedgerAccount\CreateLedgerAccount;
use FluxErp\Actions\LedgerAccount\DeleteLedgerAccount;
use FluxErp\Actions\LedgerAccount\UpdateLedgerAccount;
use FluxErp\Actions\Location\CreateLocation;
use FluxErp\Actions\Location\DeleteLocation;
use FluxErp\Actions\Location\UpdateLocation;
use FluxErp\Actions\MailAccount\CreateMailAccount;
use FluxErp\Actions\MailAccount\DeleteMailAccount;
use FluxErp\Actions\MailAccount\UpdateMailAccount;
use FluxErp\Actions\MailFolder\CreateMailFolder;
use FluxErp\Actions\MailFolder\DeleteMailFolder;
use FluxErp\Actions\MailFolder\UpdateMailFolder;
use FluxErp\Actions\Media\DeleteMedia;
use FluxErp\Actions\Media\DeleteMediaCollection;
use FluxErp\Actions\Media\DownloadMedia;
use FluxErp\Actions\Media\DownloadMultipleMedia;
use FluxErp\Actions\Media\ReplaceMedia;
use FluxErp\Actions\Media\UpdateMedia;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Actions\MediaFolder\CreateMediaFolder;
use FluxErp\Actions\MediaFolder\DeleteMediaFolder;
use FluxErp\Actions\MediaFolder\UpdateMediaFolder;
use FluxErp\Actions\NotificationSetting\UpdateNotificationSetting;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\Order\ReplicateOrder;
use FluxErp\Actions\Order\ResetPaymentReminderLevel;
use FluxErp\Actions\Order\ToggleLock;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Actions\OrderPosition\DeleteOrderPosition;
use FluxErp\Actions\OrderPosition\FillOrderPositions;
use FluxErp\Actions\OrderPosition\UpdateOrderPosition;
use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Actions\OrderTransaction\DeleteOrderTransaction;
use FluxErp\Actions\OrderTransaction\UpdateOrderTransaction;
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
use FluxErp\Actions\PrinterUser\CreatePrinterUser;
use FluxErp\Actions\PrinterUser\DeletePrinterUser;
use FluxErp\Actions\PrinterUser\UpdatePrinterUser;
use FluxErp\Actions\Printing;
use FluxErp\Actions\PrintJob\CreatePrintJob;
use FluxErp\Actions\PrintJob\DeletePrintJob;
use FluxErp\Actions\PrintJob\UpdatePrintJob;
use FluxErp\Actions\Product\CreateProduct;
use FluxErp\Actions\Product\DeleteProduct;
use FluxErp\Actions\Product\ProductBundleProduct\CreateProductBundleProduct;
use FluxErp\Actions\Product\ProductBundleProduct\DeleteProductBundleProduct;
use FluxErp\Actions\Product\ProductBundleProduct\UpdateProductBundleProduct;
use FluxErp\Actions\Product\RestoreProduct;
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
use FluxErp\Actions\ProductPropertyGroup\CreateProductPropertyGroup;
use FluxErp\Actions\ProductPropertyGroup\DeleteProductPropertyGroup;
use FluxErp\Actions\ProductPropertyGroup\UpdateProductPropertyGroup;
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
use FluxErp\Actions\Schedule\CreateSchedule;
use FluxErp\Actions\Schedule\DeleteSchedule;
use FluxErp\Actions\Schedule\UpdateSchedule;
use FluxErp\Actions\SepaMandate\CreateSepaMandate;
use FluxErp\Actions\SepaMandate\DeleteSepaMandate;
use FluxErp\Actions\SepaMandate\UpdateSepaMandate;
use FluxErp\Actions\SerialNumber\CreateSerialNumber;
use FluxErp\Actions\SerialNumber\DeleteSerialNumber;
use FluxErp\Actions\SerialNumber\UpdateSerialNumber;
use FluxErp\Actions\SerialNumberRange\CreateSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\DeleteSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\UpdateSerialNumberRange;
use FluxErp\Actions\Setting\UpdateSetting;
use FluxErp\Actions\StockPosting\CreateStockPosting;
use FluxErp\Actions\StockPosting\DeleteStockPosting;
use FluxErp\Actions\StockPosting\UpdateStockPosting;
use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Actions\Tag\DeleteTag;
use FluxErp\Actions\Tag\UpdateTag;
use FluxErp\Actions\Target\CreateTarget;
use FluxErp\Actions\Target\DeleteTarget;
use FluxErp\Actions\Target\UpdateTarget;
use FluxErp\Actions\Task\CreateTask;
use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\ReplicateTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Actions\Tenant\CreateTenant;
use FluxErp\Actions\Tenant\DeleteTenant;
use FluxErp\Actions\Tenant\UpdateTenant;
use FluxErp\Actions\Ticket\CreateTicket;
use FluxErp\Actions\Ticket\DeleteTicket;
use FluxErp\Actions\Ticket\ToggleTicketUser;
use FluxErp\Actions\Ticket\UpdateTicket;
use FluxErp\Actions\TicketType\CreateTicketType;
use FluxErp\Actions\TicketType\DeleteTicketType;
use FluxErp\Actions\TicketType\UpdateTicketType;
use FluxErp\Actions\Token\CreateToken;
use FluxErp\Actions\Token\DeleteToken;
use FluxErp\Actions\Transaction\CreateTransaction;
use FluxErp\Actions\Transaction\DeleteTransaction;
use FluxErp\Actions\Transaction\UpdateTransaction;
use FluxErp\Actions\Unit\CreateUnit;
use FluxErp\Actions\Unit\DeleteUnit;
use FluxErp\Actions\Unit\UpdateUnit;
use FluxErp\Actions\User\CreateUser;
use FluxErp\Actions\User\DeleteUser;
use FluxErp\Actions\User\UpdateUser;
use FluxErp\Actions\VacationBlackout\CreateVacationBlackout;
use FluxErp\Actions\VacationBlackout\DeleteVacationBlackout;
use FluxErp\Actions\VacationBlackout\UpdateVacationBlackout;
use FluxErp\Actions\VacationCarryoverRule\CreateVacationCarryoverRule;
use FluxErp\Actions\VacationCarryoverRule\DeleteVacationCarryoverRule;
use FluxErp\Actions\VacationCarryoverRule\UpdateVacationCarryoverRule;
use FluxErp\Actions\VatRate\CreateVatRate;
use FluxErp\Actions\VatRate\DeleteVatRate;
use FluxErp\Actions\VatRate\UpdateVatRate;
use FluxErp\Actions\Warehouse\CreateWarehouse;
use FluxErp\Actions\Warehouse\DeleteWarehouse;
use FluxErp\Actions\Warehouse\UpdateWarehouse;
use FluxErp\Actions\WorkTime\CreateWorkTime;
use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateWorkTime;
use FluxErp\Actions\WorkTimeModel\CreateWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\DeleteWorkTimeModel;
use FluxErp\Actions\WorkTimeModel\UpdateWorkTimeModel;
use FluxErp\Actions\WorkTimeType\CreateWorkTimeType;
use FluxErp\Actions\WorkTimeType\DeleteWorkTimeType;
use FluxErp\Actions\WorkTimeType\UpdateWorkTimeType;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Controllers\AuthController;
use FluxErp\Http\Controllers\BaseController;
use FluxErp\Http\Controllers\CommentController;
use FluxErp\Http\Controllers\EventSubscriptionController;
use FluxErp\Http\Controllers\MobileController;
use FluxErp\Http\Controllers\PermissionController;
use FluxErp\Http\Controllers\PrintController;
use FluxErp\Http\Controllers\RoleController;
use FluxErp\Http\Controllers\SettingController;
use FluxErp\Http\Middleware\SetAcceptHeaders;
use FluxErp\Models\AbsencePolicy;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use FluxErp\Models\Category;
use FluxErp\Models\Comment;
use FluxErp\Models\Commission;
use FluxErp\Models\CommissionRate;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\ContactOption;
use FluxErp\Models\Country;
use FluxErp\Models\CountryRegion;
use FluxErp\Models\Currency;
use FluxErp\Models\Discount;
use FluxErp\Models\DiscountGroup;
use FluxErp\Models\EmailTemplate;
use FluxErp\Models\Employee;
use FluxErp\Models\EmployeeBalanceAdjustment;
use FluxErp\Models\EmployeeDay;
use FluxErp\Models\EmployeeDepartment;
use FluxErp\Models\EventSubscription;
use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Models\Holiday;
use FluxErp\Models\Industry;
use FluxErp\Models\Language;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadLossReason;
use FluxErp\Models\LeadState;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Location;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Models\MediaFolder;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\Pivots\PrinterUser;
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
use FluxErp\Models\ProductPropertyGroup;
use FluxErp\Models\Project;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\RecordOrigin;
use FluxErp\Models\Role;
use FluxErp\Models\Schedule;
use FluxErp\Models\SepaMandate;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Tag;
use FluxErp\Models\Target;
use FluxErp\Models\Task;
use FluxErp\Models\Tenant;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\Transaction;
use FluxErp\Models\Unit;
use FluxErp\Models\User;
use FluxErp\Models\VacationBlackout;
use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeModel;
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
        Route::get('/health', [MobileController::class, 'health']);
        Route::get('/mobile/config', [MobileController::class, 'config']);
        Route::delete('/mobile/device-token/{deviceId}', [MobileController::class, 'deleteDeviceToken']);

        Route::post('/auth/token', [AuthController::class, 'authenticate']);

        Route::middleware(['auth:sanctum,token', 'ability:user,interface', 'localization', 'permission', 'api'])
            ->name('api.')
            ->group(callback: function (): void {
                // Validate Token
                Route::get('/auth/token/validate', [AuthController::class, 'validateToken']);
                Route::post('/logout', [AuthController::class, 'logout']);

                // AbsencePolicies
                Route::get('/absence-policies/{id}', [BaseController::class, 'show'])
                    ->defaults('model', AbsencePolicy::class);
                Route::get('/absence-policies', [BaseController::class, 'index'])
                    ->defaults('model', AbsencePolicy::class);
                Route::post('/absence-policies', CreateAbsencePolicy::class);
                Route::put('/absence-policies', UpdateAbsencePolicy::class);
                Route::delete('/absence-policies/{id}', DeleteAbsencePolicy::class);

                // AbsenceRequests
                Route::get('/absence-requests/{id}', [BaseController::class, 'show'])
                    ->defaults('model', AbsenceRequest::class);
                Route::get('/absence-requests', [BaseController::class, 'index'])
                    ->defaults('model', AbsenceRequest::class);
                Route::post('/absence-requests', CreateAbsenceRequest::class);
                Route::put('/absence-requests', UpdateAbsenceRequest::class);
                Route::delete('/absence-requests/{id}', DeleteAbsenceRequest::class);
                Route::post('/absence-requests/{id}/approve', ApproveAbsenceRequest::class);
                Route::post('/absence-requests/{id}/reject', RejectAbsenceRequest::class);
                Route::post('/absence-requests/{id}/revoke', RevokeAbsenceRequest::class);

                // AbsenceTypes
                Route::get('/absence-types/{id}', [BaseController::class, 'show'])
                    ->defaults('model', AbsenceType::class);
                Route::get('/absence-types', [BaseController::class, 'index'])
                    ->defaults('model', AbsenceType::class);
                Route::post('/absence-types', CreateAbsenceType::class);
                Route::put('/absence-types', UpdateAbsenceType::class);
                Route::delete('/absence-types/{id}', DeleteAbsenceType::class);

                // Addresses
                Route::get('/addresses/{id}', [BaseController::class, 'show'])->defaults('model', Address::class);
                Route::get('/addresses', [BaseController::class, 'index'])->defaults('model', Address::class);
                Route::post('/addresses', CreateAddress::class);
                Route::put('/addresses', UpdateAddress::class);
                Route::delete('/addresses/{id}', DeleteAddress::class);

                // AddressTypes
                Route::get('/address-types/{id}', [BaseController::class, 'show'])
                    ->defaults('model', AddressType::class);
                Route::get('/address-types', [BaseController::class, 'index'])
                    ->defaults('model', AddressType::class);
                Route::post('/address-types', CreateAddressType::class);
                Route::put('/address-types', UpdateAddressType::class);
                Route::delete('/address-types/{id}', DeleteAddressType::class);

                // AttributeTranslations
                Route::post('/attribute-translations', UpsertAttributeTranslation::class);
                Route::delete('/attribute-translations/{id}', DeleteAttributeTranslation::class);

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
                Route::post('/calendar-events/{id}/cancel', CancelCalendarEvent::class);
                Route::post('/calendar-events/{id}/reactivate', ReactivateCalendarEvent::class);

                // CartItems
                Route::get('/cart-items/{id}', [BaseController::class, 'show'])
                    ->defaults('model', CartItem::class);
                Route::get('/cart-items', [BaseController::class, 'index'])
                    ->defaults('model', CartItem::class);
                Route::post('/cart-items', CreateCartItem::class);
                Route::put('/cart-items', UpdateCartItem::class);
                Route::delete('/cart-items/{id}', DeleteCartItem::class);

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

                // CommissionRates
                Route::get('/commission-rates/{id}', [BaseController::class, 'show'])
                    ->defaults('model', CommissionRate::class);
                Route::get('/commission-rates', [BaseController::class, 'index'])
                    ->defaults('model', CommissionRate::class);
                Route::post('/commission-rates', CreateCommissionRate::class);
                Route::put('/commission-rates', UpdateCommissionRate::class);
                Route::delete('/commission-rates/{id}', DeleteCommissionRate::class);

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
                Route::post('/contacts/{id}/restore', RestoreContact::class);

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

                // DiscountGroups
                Route::get('/discount-groups/{id}', [BaseController::class, 'show'])
                    ->defaults('model', DiscountGroup::class);
                Route::get('/discount-groups', [BaseController::class, 'index'])
                    ->defaults('model', DiscountGroup::class);
                Route::post('/discount-groups', CreateDiscountGroup::class);
                Route::put('/discount-groups', UpdateDiscountGroup::class);
                Route::delete('/discount-groups/{id}', DeleteDiscountGroup::class);

                // Discounts
                Route::get('/discounts/{id}', [BaseController::class, 'show'])->defaults('model', Discount::class);
                Route::get('/discounts', [BaseController::class, 'index'])->defaults('model', Discount::class);
                Route::post('/discounts', CreateDiscount::class);
                Route::put('/discounts', UpdateDiscount::class);
                Route::delete('/discounts/{id}', DeleteDiscount::class);

                // EmailTemplates
                Route::get('/email-templates/{id}', [BaseController::class, 'show'])
                    ->defaults('model', EmailTemplate::class);
                Route::get('/email-templates', [BaseController::class, 'index'])
                    ->defaults('model', EmailTemplate::class);
                Route::post('/email-templates', CreateEmailTemplate::class);
                Route::put('/email-templates', UpdateEmailTemplate::class);
                Route::delete('/email-templates/{id}', DeleteEmailTemplate::class);

                // EmployeeBalanceAdjustments
                Route::get('/employee-balance-adjustments/{id}', [BaseController::class, 'show'])
                    ->defaults('model', EmployeeBalanceAdjustment::class);
                Route::get('/employee-balance-adjustments', [BaseController::class, 'index'])
                    ->defaults('model', EmployeeBalanceAdjustment::class);
                Route::post('/employee-balance-adjustments', CreateEmployeeBalanceAdjustment::class);
                Route::put('/employee-balance-adjustments', UpdateEmployeeBalanceAdjustment::class);
                Route::delete('/employee-balance-adjustments/{id}', DeleteEmployeeBalanceAdjustment::class);

                // EmployeeDays
                Route::get('/employee-days/{id}', [BaseController::class, 'show'])
                    ->defaults('model', EmployeeDay::class);
                Route::get('/employee-days', [BaseController::class, 'index'])
                    ->defaults('model', EmployeeDay::class);
                Route::post('/employee-days', CreateEmployeeDay::class);
                Route::put('/employee-days', UpdateEmployeeDay::class);
                Route::delete('/employee-days/{id}', DeleteEmployeeDay::class);
                Route::post('/employee-days/close', CloseEmployeeDay::class);

                // EmployeeDepartments
                Route::get('/employee-departments/{id}', [BaseController::class, 'show'])
                    ->defaults('model', EmployeeDepartment::class);
                Route::get('/employee-departments', [BaseController::class, 'index'])
                    ->defaults('model', EmployeeDepartment::class);
                Route::post('/employee-departments', CreateEmployeeDepartment::class);
                Route::put('/employee-departments', UpdateEmployeeDepartment::class);
                Route::delete('/employee-departments/{id}', DeleteEmployeeDepartment::class);

                // Employees
                Route::get('/employees/{id}', [BaseController::class, 'show'])
                    ->defaults('model', Employee::class);
                Route::get('/employees', [BaseController::class, 'index'])
                    ->defaults('model', Employee::class);
                Route::post('/employees', CreateEmployee::class);
                Route::put('/employees', UpdateEmployee::class);
                Route::delete('/employees/{id}', DeleteEmployee::class);

                // EmployeeWorkTimeModels
                Route::get('/employee-work-time-models/{id}', [BaseController::class, 'show'])
                    ->defaults('model', EmployeeWorkTimeModel::class);
                Route::get('/employee-work-time-models', [BaseController::class, 'index'])
                    ->defaults('model', EmployeeWorkTimeModel::class);
                Route::post('/employee-work-time-models', CreateEmployeeWorkTimeModel::class);
                Route::put('/employee-work-time-models', UpdateEmployeeWorkTimeModel::class);
                Route::delete('/employee-work-time-models/{id}', DeleteEmployeeWorkTimeModel::class);

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

                // Holidays
                Route::get('/holidays/{id}', [BaseController::class, 'show'])
                    ->defaults('model', Holiday::class);
                Route::get('/holidays', [BaseController::class, 'index'])
                    ->defaults('model', Holiday::class);
                Route::post('/holidays', CreateHoliday::class);
                Route::put('/holidays', UpdateHoliday::class);
                Route::delete('/holidays/{id}', DeleteHoliday::class);

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

                // Leads
                Route::get('/leads/{id}', [BaseController::class, 'show'])->defaults('model', Lead::class);
                Route::get('/leads', [BaseController::class, 'index'])->defaults('model', Lead::class);
                Route::post('/leads', CreateLead::class);
                Route::put('/leads', UpdateLead::class);
                Route::delete('/leads/{id}', DeleteLead::class);

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

                // Locations
                Route::get('/locations/{id}', [BaseController::class, 'show'])
                    ->defaults('model', Location::class);
                Route::get('/locations', [BaseController::class, 'index'])
                    ->defaults('model', Location::class);
                Route::post('/locations', CreateLocation::class);
                Route::put('/locations', UpdateLocation::class);
                Route::delete('/locations/{id}', DeleteLocation::class);

                // MailAccounts
                Route::get('/mail-accounts/{id}', [BaseController::class, 'show'])
                    ->defaults('model', MailAccount::class);
                Route::get('/mail-accounts', [BaseController::class, 'index'])->defaults('model', MailAccount::class);
                Route::post('/mail-accounts', CreateMailAccount::class);
                Route::put('/mail-accounts', UpdateMailAccount::class);
                Route::delete('/mail-accounts/{id}', DeleteMailAccount::class);

                // MailFolders
                Route::get('/mail-folders/{id}', [BaseController::class, 'show'])
                    ->defaults('model', MailFolder::class);
                Route::get('/mail-folders', [BaseController::class, 'index'])
                    ->defaults('model', MailFolder::class);
                Route::post('/mail-folders', CreateMailFolder::class);
                Route::put('/mail-folders', UpdateMailFolder::class);
                Route::delete('/mail-folders/{id}', DeleteMailFolder::class);

                // Media
                Route::get('/media/private/{id}', DownloadMedia::class)
                    ->withoutMiddleware(SetAcceptHeaders::class);
                Route::get('/media/download-multiple', DownloadMultipleMedia::class);
                Route::post('/media/{id}', ReplaceMedia::class);
                Route::post('/media', UploadMedia::class);
                Route::put('/media', UpdateMedia::class);
                Route::delete('/media/{id}', DeleteMedia::class);
                Route::delete('/media-collections', DeleteMediaCollection::class);

                // MediaFolders
                Route::get('/media-folders/{id}', [BaseController::class, 'show'])
                    ->defaults('model', MediaFolder::class);
                Route::get('/media-folders', [BaseController::class, 'index'])
                    ->defaults('model', MediaFolder::class);
                Route::post('/media-folders', CreateMediaFolder::class);
                Route::put('/media-folders', UpdateMediaFolder::class);
                Route::delete('/media-folders/{id}', DeleteMediaFolder::class);

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
                Route::post('/orders/{id}/replicate', ReplicateOrder::class);
                Route::post('/orders/{id}/reset-payment-reminder-level', ResetPaymentReminderLevel::class);

                // OrderPositions
                Route::get('/order-positions/{id}', [BaseController::class, 'show'])
                    ->defaults('model', OrderPosition::class);
                Route::get('/order-positions', [BaseController::class, 'index'])
                    ->defaults('model', OrderPosition::class);
                Route::post('/order-positions', CreateOrderPosition::class);
                Route::post('/order-positions/fill', FillOrderPositions::class);
                Route::put('/order-positions', UpdateOrderPosition::class);
                Route::delete('/order-positions/{id}', DeleteOrderPosition::class);

                // OrderTransactions
                Route::get('/order-transactions/{id}', [BaseController::class, 'show'])
                    ->defaults('model', OrderTransaction::class);
                Route::get('/order-transactions', [BaseController::class, 'index'])
                    ->defaults('model', OrderTransaction::class);
                Route::post('/order-transactions', CreateOrderTransaction::class);
                Route::put('/order-transactions', UpdateOrderTransaction::class);
                Route::delete('/order-transactions/{id}', DeleteOrderTransaction::class);

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

                // PrinterUsers
                Route::get('/printer-users/{id}', [BaseController::class, 'show'])
                    ->defaults('model', PrinterUser::class);
                Route::get('/printer-users', [BaseController::class, 'index'])
                    ->defaults('model', PrinterUser::class);
                Route::post('/printer-users', CreatePrinterUser::class);
                Route::put('/printer-users', UpdatePrinterUser::class);
                Route::delete('/printer-users/{id}', DeletePrinterUser::class);

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
                Route::post('/products/{id}/restore', RestoreProduct::class);

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

                // ProductPropertyGroups
                Route::get('/product-property-groups/{id}', [BaseController::class, 'show'])
                    ->defaults('model', ProductPropertyGroup::class);
                Route::get('/product-property-groups', [BaseController::class, 'index'])
                    ->defaults('model', ProductPropertyGroup::class);
                Route::post('/product-property-groups', CreateProductPropertyGroup::class);
                Route::put('/product-property-groups', UpdateProductPropertyGroup::class);
                Route::delete('/product-property-groups/{id}', DeleteProductPropertyGroup::class);

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

                // Schedules
                Route::get('/schedules/{id}', [BaseController::class, 'show'])
                    ->defaults('model', Schedule::class);
                Route::get('/schedules', [BaseController::class, 'index'])
                    ->defaults('model', Schedule::class);
                Route::post('/schedules', CreateSchedule::class);
                Route::put('/schedules', UpdateSchedule::class);
                Route::delete('/schedules/{id}', DeleteSchedule::class);

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
                Route::get('/settings', [SettingController::class, 'getSettings']);
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
                Route::put('/stock-postings', UpdateStockPosting::class);
                Route::delete('/stock-postings/{id}', DeleteStockPosting::class);

                // Tag
                Route::get('/tags/{id}', [BaseController::class, 'show'])->defaults('model', Tag::class);
                Route::get('/tags', [BaseController::class, 'index'])->defaults('model', Tag::class);
                Route::post('/tags', CreateTag::class);
                Route::put('/tags', UpdateTag::class);
                Route::delete('/tags/{id}', DeleteTag::class);

                // Targets
                Route::get('/targets/{id}', [BaseController::class, 'show'])->defaults('model', Target::class);
                Route::get('/targets', [BaseController::class, 'index'])->defaults('model', Target::class);
                Route::post('/targets', CreateTarget::class);
                Route::put('/targets', UpdateTarget::class);
                Route::delete('/targets/{id}', DeleteTarget::class);

                // Tasks
                Route::get('/tasks/{id}', [BaseController::class, 'show'])->defaults('model', Task::class);
                Route::get('/tasks', [BaseController::class, 'index'])->defaults('model', Task::class);
                Route::post('/tasks', CreateTask::class);
                Route::put('/tasks', UpdateTask::class);
                Route::delete('/tasks/{id}', DeleteTask::class);
                Route::post('/tasks/{id}/replicate', ReplicateTask::class);

                // Tenants
                Route::get('/tenants/{id}', [BaseController::class, 'show'])->defaults('model', Tenant::class);
                Route::get('/tenants', [BaseController::class, 'index'])->defaults('model', Tenant::class);
                Route::post('/tenants', CreateTenant::class);
                Route::put('/tenants', UpdateTenant::class);
                Route::delete('/tenants/{id}', DeleteTenant::class);

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

                // Tokens
                Route::post('/tokens', CreateToken::class);
                Route::delete('/tokens/{id}', DeleteToken::class);

                // Transactions
                Route::get('/transactions/{id}', [BaseController::class, 'show'])
                    ->defaults('model', Transaction::class);
                Route::get('/transactions', [BaseController::class, 'index'])
                    ->defaults('model', Transaction::class);
                Route::post('/transactions', CreateTransaction::class);
                Route::put('/transactions', UpdateTransaction::class);
                Route::delete('/transactions/{id}', DeleteTransaction::class);

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

                // Units
                Route::get('/units/{id}', [BaseController::class, 'show'])->defaults('model', Unit::class);
                Route::get('/units', [BaseController::class, 'index'])->defaults('model', Unit::class);
                Route::post('/units', CreateUnit::class);
                Route::put('/units', UpdateUnit::class);
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

                // VacationBlackouts
                Route::get('/vacation-blackouts/{id}', [BaseController::class, 'show'])
                    ->defaults('model', VacationBlackout::class);
                Route::get('/vacation-blackouts', [BaseController::class, 'index'])
                    ->defaults('model', VacationBlackout::class);
                Route::post('/vacation-blackouts', CreateVacationBlackout::class);
                Route::put('/vacation-blackouts', UpdateVacationBlackout::class);
                Route::delete('/vacation-blackouts/{id}', DeleteVacationBlackout::class);

                // VacationCarryoverRules
                Route::get('/vacation-carryover-rules/{id}', [BaseController::class, 'show'])
                    ->defaults('model', VacationCarryoverRule::class);
                Route::get('/vacation-carryover-rules', [BaseController::class, 'index'])
                    ->defaults('model', VacationCarryoverRule::class);
                Route::post('/vacation-carryover-rules', CreateVacationCarryoverRule::class);
                Route::put('/vacation-carryover-rules', UpdateVacationCarryoverRule::class);
                Route::delete('/vacation-carryover-rules/{id}', DeleteVacationCarryoverRule::class);

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

                // WorkTimeModels
                Route::get('/work-time-models/{id}', [BaseController::class, 'show'])
                    ->defaults('model', WorkTimeModel::class);
                Route::get('/work-time-models', [BaseController::class, 'index'])
                    ->defaults('model', WorkTimeModel::class);
                Route::post('/work-time-models', CreateWorkTimeModel::class);
                Route::put('/work-time-models', UpdateWorkTimeModel::class);
                Route::delete('/work-time-models/{id}', DeleteWorkTimeModel::class);
                Route::post('/work-time-models/assign', AssignWorkTimeModel::class);
            });

        Route::get('/media/{file_name}', DownloadMedia::class)->name('media.public');
        Broadcast::routes(['middleware' => ['auth:sanctum']]);
    });
