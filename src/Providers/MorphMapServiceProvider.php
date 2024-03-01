<?php

namespace FluxErp\Providers;

use FluxErp\Models\Activity;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
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
use FluxErp\Models\CustomEvent;
use FluxErp\Models\Discount;
use FluxErp\Models\DiscountGroup;
use FluxErp\Models\DocumentType;
use FluxErp\Models\Favorite;
use FluxErp\Models\FormBuilderField;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Models\InterfaceUser;
use FluxErp\Models\Language;
use FluxErp\Models\LanguageLine;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Log;
use FluxErp\Models\MailAccount;
use FluxErp\Models\MailFolder;
use FluxErp\Models\Media;
use FluxErp\Models\Notification;
use FluxErp\Models\NotificationSetting;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\Price;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\ProductCrossSelling;
use FluxErp\Models\ProductOption;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Models\ProductProperty;
use FluxErp\Models\Project;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\Role;
use FluxErp\Models\Schedule;
use FluxErp\Models\SepaMandate;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Models\Setting;
use FluxErp\Models\Snapshot;
use FluxErp\Models\StateSetting;
use FluxErp\Models\StockPosting;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\Token;
use FluxErp\Models\Transaction;
use FluxErp\Models\Unit;
use FluxErp\Models\User;
use FluxErp\Models\VatRate;
use FluxErp\Models\Warehouse;
use FluxErp\Models\Widget;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class MorphMapServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Relation::enforceMorphMap([
            'activity' => Activity::class,
            'additional_column' => AdditionalColumn::class,
            'address' => Address::class,
            'address_type' => AddressType::class,
            'bank_connection' => BankConnection::class,
            'calendar' => Calendar::class,
            'calendar_event' => CalendarEvent::class,
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
            'custom_event' => CustomEvent::class,
            'discount' => Discount::class,
            'discount_group' => DiscountGroup::class,
            'document_type' => DocumentType::class,
            'favorite' => Favorite::class,
            'form_builder_field' => FormBuilderField::class,
            'form_builder_field_response' => FormBuilderFieldResponse::class,
            'form_builder_form' => FormBuilderForm::class,
            'form_builder_response' => FormBuilderResponse::class,
            'form_builder_section' => FormBuilderSection::class,
            'interface_user' => InterfaceUser::class,
            'language' => Language::class,
            'translation' => LanguageLine::class,
            'ledger_account' => LedgerAccount::class,
            'log' => Log::class,
            'mail_account' => MailAccount::class,
            'mail_folder' => MailFolder::class,
            'media' => Media::class,
            'notification' => Notification::class,
            'notification_setting' => NotificationSetting::class,
            'order' => Order::class,
            'order_position' => OrderPosition::class,
            'order_type' => OrderType::class,
            'payment_reminder' => PaymentReminder::class,
            'payment_run' => PaymentRun::class,
            'payment_type' => PaymentType::class,
            'permission' => Permission::class,
            'price' => Price::class,
            'price_list' => PriceList::class,
            'product' => Product::class,
            'product_cross_selling' => ProductCrossSelling::class,
            'product_option' => ProductOption::class,
            'product_option_group' => ProductOptionGroup::class,
            'product_property' => ProductProperty::class,
            'project' => Project::class,
            'purchase_invoice' => PurchaseInvoice::class,
            'purchase_invoice_position' => PurchaseInvoicePosition::class,
            'role' => Role::class,
            'schedule' => Schedule::class,
            'sepa_mandate' => SepaMandate::class,
            'serial_number' => SerialNumber::class,
            'serial_number_range' => SerialNumberRange::class,
            'setting' => Setting::class,
            'snapshot' => Snapshot::class,
            'stock_posting' => StockPosting::class,
            'tag' => Tag::class,
            'task' => Task::class,
            'ticket' => Ticket::class,
            'ticket_type' => TicketType::class,
            'token' => Token::class,
            'transaction' => Transaction::class,
            'unit' => Unit::class,
            'user' => User::class,
            'vat_rate' => VatRate::class,
            'warehouse' => Warehouse::class,
            'widget' => Widget::class,
            'work_time' => WorkTime::class,
            'work_time_type' => WorkTimeType::class,
        ]);
    }
}
