<?php

namespace FluxErp\Providers;

use FluxErp\Models\AbsencePolicy;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Activity;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\AttributeTranslation;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\CalendarUserSetting;
use FluxErp\Models\Cart;
use FluxErp\Models\CartItem;
use FluxErp\Models\Category;
use FluxErp\Models\Client;
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
use FluxErp\Models\FailedJob;
use FluxErp\Models\Favorite;
use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Models\Holiday;
use FluxErp\Models\Industry;
use FluxErp\Models\JobBatch;
use FluxErp\Models\Language;
use FluxErp\Models\LanguageLine;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadLossReason;
use FluxErp\Models\LeadState;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Location;
use FluxErp\Models\Lock;
use FluxErp\Models\Log;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Models\Media;
use FluxErp\Models\MediaFolder;
use FluxErp\Models\Meta;
use FluxErp\Models\Notification;
use FluxErp\Models\NotificationSetting;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentReminderText;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\Pivots\AbsencePolicyAbsenceType;
use FluxErp\Models\Pivots\AbsenceRequestEmployeeDay;
use FluxErp\Models\Pivots\AbsenceRequestSubstitute;
use FluxErp\Models\Pivots\AddressAddressTypeOrder;
use FluxErp\Models\Pivots\Calendarable;
use FluxErp\Models\Pivots\CalendarEventInvite;
use FluxErp\Models\Pivots\Categorizable;
use FluxErp\Models\Pivots\ClientPaymentType;
use FluxErp\Models\Pivots\ClientProduct;
use FluxErp\Models\Pivots\Communicatable;
use FluxErp\Models\Pivots\ContactDiscount;
use FluxErp\Models\Pivots\ContactDiscountGroup;
use FluxErp\Models\Pivots\ContactIndustry;
use FluxErp\Models\Pivots\EmployeeDayWorkTime;
use FluxErp\Models\Pivots\EmployeeDepartmentVacationBlackout;
use FluxErp\Models\Pivots\EmployeeVacationBlackout;
use FluxErp\Models\Pivots\EmployeeWorkTimeModel;
use FluxErp\Models\Pivots\HolidayLocation;
use FluxErp\Models\Pivots\Inviteable;
use FluxErp\Models\Pivots\JobBatchable;
use FluxErp\Models\Pivots\LocationVacationBlackout;
use FluxErp\Models\Pivots\MediaFolderModel;
use FluxErp\Models\Pivots\OrderSchedule;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Models\Pivots\PrinterUser;
use FluxErp\Models\Pivots\ProductBundleProduct;
use FluxErp\Models\Pivots\ProductCrossSellingProduct;
use FluxErp\Models\Pivots\ProductProductOption;
use FluxErp\Models\Pivots\QueueMonitorable;
use FluxErp\Models\Pivots\TargetUser;
use FluxErp\Models\Pivots\TaskUser;
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
use FluxErp\Models\PushSubscription;
use FluxErp\Models\QueueMonitor;
use FluxErp\Models\RecordOrigin;
use FluxErp\Models\Role;
use FluxErp\Models\Schedule;
use FluxErp\Models\SepaMandate;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Models\Snapshot;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Tag;
use FluxErp\Models\Target;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\Token;
use FluxErp\Models\Transaction;
use FluxErp\Models\Unit;
use FluxErp\Models\User;
use FluxErp\Models\VacationBlackout;
use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Models\Widget;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeModel;
use FluxErp\Models\WorkTimeModelSchedule;
use FluxErp\Models\WorkTimeType;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class MorphMapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Relation::enforceMorphMap([
            'absence_policy' => AbsencePolicy::class,
            'absence_request' => AbsenceRequest::class,
            'absence_type' => AbsenceType::class,
            'activity' => Activity::class,
            'additional_column' => AdditionalColumn::class,
            'address' => Address::class,
            'address_type' => AddressType::class,
            'attribute_translation' => AttributeTranslation::class,
            'bank_connection' => BankConnection::class,
            'calendar' => Calendar::class,
            'calendar_event' => CalendarEvent::class,
            'calendar_user_setting' => CalendarUserSetting::class,
            'cart' => Cart::class,
            'cart_item' => CartItem::class,
            'category' => Category::class,
            'client' => Client::class,
            'comment' => Comment::class,
            'commission' => Commission::class,
            'commission_rate' => CommissionRate::class,
            'communication' => Communication::class,
            'contact' => Contact::class,
            'contact_bank_connection' => ContactBankConnection::class,
            'contact_option' => ContactOption::class,
            'country' => Country::class,
            'country_region' => CountryRegion::class,
            'currency' => Currency::class,
            'discount' => Discount::class,
            'discount_group' => DiscountGroup::class,
            'email_template' => EmailTemplate::class,
            'employee' => Employee::class,
            'employee_balance_adjustment' => EmployeeBalanceAdjustment::class,
            'employee_day' => EmployeeDay::class,
            'employee_department' => EmployeeDepartment::class,
            'event_subscription' => EventSubscription::class,
            'failed_job' => FailedJob::class,
            'favorite' => Favorite::class,
            'form_builder_field' => FormBuilderField::class,
            'form_builder_field_response' => FormBuilderFieldResponse::class,
            'form_builder_form' => FormBuilderForm::class,
            'form_builder_response' => FormBuilderResponse::class,
            'form_builder_section' => FormBuilderSection::class,
            'holiday' => Holiday::class,
            'industry' => Industry::class,
            'job_batch' => JobBatch::class,
            'language' => Language::class,
            'language_line' => LanguageLine::class,
            'lead' => Lead::class,
            'lead_loss_reason' => LeadLossReason::class,
            'lead_state' => LeadState::class,
            'ledger_account' => LedgerAccount::class,
            'location' => Location::class,
            'lock' => Lock::class,
            'log' => Log::class,
            'mail_account' => MailAccount::class,
            'mail_folder' => MailFolder::class,
            'media' => Media::class,
            'media_folder' => MediaFolder::class,
            'meta' => Meta::class,
            'notification' => Notification::class,
            'database_notification' => Notification::class,
            'notification_setting' => NotificationSetting::class,
            'order' => Order::class,
            'order_position' => OrderPosition::class,
            'order_type' => OrderType::class,
            'payment_reminder' => PaymentReminder::class,
            'payment_reminder_text' => PaymentReminderText::class,
            'payment_run' => PaymentRun::class,
            'payment_type' => PaymentType::class,
            'permission' => Permission::class,
            'price' => Price::class,
            'price_list' => PriceList::class,
            'print_job' => PrintJob::class,
            'printer' => Printer::class,
            'product' => Product::class,
            'product_cross_selling' => ProductCrossSelling::class,
            'product_option' => ProductOption::class,
            'product_option_group' => ProductOptionGroup::class,
            'product_property' => ProductProperty::class,
            'product_property_group' => ProductPropertyGroup::class,
            'project' => Project::class,
            'purchase_invoice' => PurchaseInvoice::class,
            'purchase_invoice_position' => PurchaseInvoicePosition::class,
            'push_subscription' => PushSubscription::class,
            'queue_monitor' => QueueMonitor::class,
            'record_origin' => RecordOrigin::class,
            'role' => Role::class,
            'schedule' => Schedule::class,
            'sepa_mandate' => SepaMandate::class,
            'serial_number' => SerialNumber::class,
            'serial_number_range' => SerialNumberRange::class,
            'snapshot' => Snapshot::class,
            'stock_posting' => StockPosting::class,
            'tag' => Tag::class,
            'target' => Target::class,
            'task' => Task::class,
            'ticket' => Ticket::class,
            'ticket_type' => TicketType::class,
            'token' => Token::class,
            'transaction' => Transaction::class,
            'unit' => Unit::class,
            'user' => User::class,
            'vacation_blackout' => VacationBlackout::class,
            'vacation_carryover_rule' => VacationCarryoverRule::class,
            'vat_rate' => VatRate::class,
            'warehouse' => Warehouse::class,
            'widget' => Widget::class,
            'work_time' => WorkTime::class,
            'work_time_model' => WorkTimeModel::class,
            'work_time_model_schedule' => WorkTimeModelSchedule::class,
            'work_time_type' => WorkTimeType::class,

            'absence_policy_absence_type' => AbsencePolicyAbsenceType::class,
            'absence_request_employee_day' => AbsenceRequestEmployeeDay::class,
            'absence_request_substitute' => AbsenceRequestSubstitute::class,
            'address_address_type_order' => AddressAddressTypeOrder::class,
            'calendarable' => Calendarable::class,
            'calendar_event_invitee' => CalendarEventInvite::class,
            'categorizable' => Categorizable::class,
            'client_payment_type' => ClientPaymentType::class,
            'client_product' => ClientProduct::class,
            'communicatable' => Communicatable::class,
            'contact_discount' => ContactDiscount::class,
            'contact_discount_group' => ContactDiscountGroup::class,
            'contact_industry' => ContactIndustry::class,
            'employee_day_work_time' => EmployeeDayWorkTime::class,
            'employee_department_vacation_blackout' => EmployeeDepartmentVacationBlackout::class,
            'employee_vacation_blackout' => EmployeeVacationBlackout::class,
            'employee_work_time_model' => EmployeeWorkTimeModel::class,
            'holiday_location' => HolidayLocation::class,
            'invitable' => Inviteable::class,
            'job_batchable' => JobBatchable::class,
            'location_vacation_blackout' => LocationVacationBlackout::class,
            'media_folder_model' => MediaFolderModel::class,
            'order_schedule' => OrderSchedule::class,
            'order_transaction' => OrderTransaction::class,
            'printer_user' => PrinterUser::class,
            'product_bundle_product' => ProductBundleProduct::class,
            'product_cross_selling_product' => ProductCrossSellingProduct::class,
            'product_product_option' => ProductProductOption::class,
            'queue_monitorable' => QueueMonitorable::class,
            'target_user' => TargetUser::class,
            'task_user' => TaskUser::class,
        ]);
    }
}
