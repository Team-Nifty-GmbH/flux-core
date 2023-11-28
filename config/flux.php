<?php

return [
    'portal_domain' => env(
        'PORTAL_DOMAIN',
        'portal.' . preg_replace('(^https?://)', '', env('APP_URL'))
    ),
    'rate_limit' => env('API_RATE_LIMIT', 60),

    'media' => [
        'conversion' => env('MEDIA_CONVERSIONS_DISK', 'public'),
        'disk' => env('MEDIA_DISK', 'local'),
    ],

    'gotenberg' => [
        'host' => env('GOTENBERG_HOST', 'http://localhost'),
        'port' => env('GOTENBERG_PORT', '3000'),
    ],

    'models' => [
        'activity' => \FluxErp\Models\Activity::class,
        'additional_column' => \FluxErp\Models\AdditionalColumn::class,
        'address' => \FluxErp\Models\Address::class,
        'address_type' => \FluxErp\Models\AddressType::class,
        'bank_connection' => \FluxErp\Models\BankConnection::class,
        'calendar' => \FluxErp\Models\Calendar::class,
        'calendar_event' => \FluxErp\Models\CalendarEvent::class,
        'category' => \FluxErp\Models\Category::class,
        'client' => \FluxErp\Models\Client::class,
        'comment' => \FluxErp\Models\Comment::class,
        'contact' => \FluxErp\Models\Contact::class,
        'contact_bank_connection' => \FluxErp\Models\ContactBankConnection::class,
        'contact_option' => \FluxErp\Models\ContactOption::class,
        'country' => \FluxErp\Models\Country::class,
        'country_region' => \FluxErp\Models\CountryRegion::class,
        'currency' => \FluxErp\Models\Currency::class,
        'custom_event' => \FluxErp\Models\CustomEvent::class,
        'discount' => \FluxErp\Models\Discount::class,
        'document_generation_setting' => \FluxErp\Models\DocumentGenerationSetting::class,
        'document_type' => \FluxErp\Models\DocumentType::class,
        'email' => \FluxErp\Models\Email::class,
        'email_template' => \FluxErp\Models\EmailTemplate::class,
        'event_subscription' => \FluxErp\Models\EventSubscription::class,
        'interface_user' => \FluxErp\Models\InterfaceUser::class,
        'language' => \FluxErp\Models\Language::class,
        'lock' => \FluxErp\Models\Lock::class,
        'log' => \FluxErp\Models\Log::class,
        'media' => \FluxErp\Models\Media::class,
        'meta' => \FluxErp\Models\Meta::class,
        'notification' => \FluxErp\Models\Notification::class,
        'notification_setting' => \FluxErp\Models\NotificationSetting::class,
        'order' => \FluxErp\Models\Order::class,
        'order_position' => \FluxErp\Models\OrderPosition::class,
        'order_type' => \FluxErp\Models\OrderType::class,
        'payment_notice' => \FluxErp\Models\PaymentNotice::class,
        'permission' => \FluxErp\Models\Permission::class,
        'presentation' => \FluxErp\Models\Presentation::class,
        'price' => \FluxErp\Models\Price::class,
        'price_list' => \FluxErp\Models\PriceList::class,
        'print_data' => \FluxErp\Models\PrintData::class,
        'product' => \FluxErp\Models\Product::class,
        'product_option' => \FluxErp\Models\ProductOption::class,
        'product_option_group' => \FluxErp\Models\ProductOptionGroup::class,
        'product_property' => \FluxErp\Models\ProductProperty::class,
        'project' => \FluxErp\Models\Project::class,
        'role' => \FluxErp\Models\Role::class,
        'sepa_mandate' => \FluxErp\Models\SepaMandate::class,
        'serial_number' => \FluxErp\Models\SerialNumber::class,
        'serial_number_range' => \FluxErp\Models\SerialNumberRange::class,
        'setting' => \FluxErp\Models\Setting::class,
        'snapshot' => \FluxErp\Models\Snapshot::class,
        'state_setting' => \FluxErp\Models\StateSetting::class,
        'stock_posting' => \FluxErp\Models\StockPosting::class,
        'task' => \FluxErp\Models\Task::class,
        'ticket' => \FluxErp\Models\Ticket::class,
        'ticket_type' => \FluxErp\Models\TicketType::class,
        'token' => \FluxErp\Models\Token::class,
        'transaction' => \FluxErp\Models\Transaction::class,
        'unit' => \FluxErp\Models\Unit::class,
        'user' => \FluxErp\Models\User::class,
        'vat_rate' => \FluxErp\Models\VatRate::class,
        'warehouse' => \FluxErp\Models\Warehouse::class,
    ],
];
