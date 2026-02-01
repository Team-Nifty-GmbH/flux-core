<?php

use FluxErp\Actions\Printing;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Schedule;
use FluxErp\Settings\SubscriptionSettings;
use FluxErp\View\Printing\Order\CancellationConfirmation;
use Illuminate\Contracts\Support\Htmlable;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'company' => 'Test Company GmbH',
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
    ]);

    $contact->update(['main_address_id' => $address->getKey()]);

    $this->address = $address;

    $priceList = PriceList::factory()->create();

    $currency = Currency::factory()->create([
        'is_default' => true,
    ]);

    $language = Language::query()->where('language_code', 'de')->first()
        ?? Language::factory()->create(['language_code' => 'de']);

    $orderType = OrderType::factory()
        ->create([
            'print_layouts' => ['cancellation-confirmation'],
            'tenant_id' => $this->dbTenant->getKey(),
            'order_type_enum' => OrderTypeEnum::Subscription,
        ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create([
            'is_default' => false,
        ]);

    $this->order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'language_id' => $language->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => $priceList->getKey(),
        'currency_id' => $currency->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'is_locked' => false,
        'order_number' => 'TEST-2024-001',
    ]);

    $this->contact = $contact;
});

test('can render cancellation confirmation html', function (): void {
    $this->withoutVite();

    SubscriptionSettings::fake([
        'cancellation_text' => '<p>Your subscription has been cancelled.</p>',
        'default_cancellation_notice_value' => 30,
        'default_cancellation_notice_unit' => 'days',
    ]);

    $result = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'cancellation-confirmation',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute();

    expect($result)->toBeInstanceOf(Htmlable::class);
    $html = $result->toHtml();

    $this->assertStringContainsString('Your subscription has been cancelled.', $html);
});

test('cancellation text variables are replaced', function (): void {
    $this->withoutVite();

    // Use the Editor's blade variable format
    $cancellationText = '<p>Dear <span data-type="blade-variable" data-value="$order-&gt;contact?-&gt;mainAddress?-&gt;name">Customer Name</span>, '
        . 'your contract <span data-type="blade-variable" data-value="$order-&gt;order_number">Contract Number</span> '
        . 'ends on <span data-type="blade-variable" data-value="$order-&gt;calculateSubscriptionEndDate()-&gt;locale(app()-&gt;getLocale())-&gt;isoFormat(\'L\')">Subscription End Date</span>.</p>';

    SubscriptionSettings::fake([
        'cancellation_text' => $cancellationText,
        'default_cancellation_notice_value' => 30,
        'default_cancellation_notice_unit' => 'days',
    ]);

    $schedule = Schedule::query()->create([
        'name' => 'Test Schedule',
        'class' => FluxErp\Invokable\ProcessSubscriptionOrder::class,
        'type' => FluxErp\Enums\RepeatableTypeEnum::Invokable,
        'cron' => ['dayOfMonth' => [1]],
        'ends_at' => now()->addMonth(),
    ]);

    $this->order->schedules()->attach($schedule);

    $result = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'cancellation-confirmation',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute();

    $html = $result->toHtml();

    $this->assertStringContainsString($this->address->fresh()->name, $html);
    $this->assertStringContainsString('TEST-2024-001', $html);
    $this->assertStringNotContainsString('data-type="blade-variable"', $html);
});

test('empty cancellation text renders without error', function (): void {
    $this->withoutVite();

    SubscriptionSettings::fake([
        'cancellation_text' => null,
        'default_cancellation_notice_value' => 0,
        'default_cancellation_notice_unit' => 'days',
    ]);

    $result = Printing::make([
        'model_type' => $this->order->getMorphClass(),
        'model_id' => $this->order->getKey(),
        'view' => 'cancellation-confirmation',
        'preview' => false,
        'html' => true,
    ])
        ->validate()
        ->execute();

    expect($result)->toBeInstanceOf(Htmlable::class);
    // The page should render but the cancellation text area should be empty
    $html = $result->toHtml();
    $this->assertStringContainsString('TEST-2024-001', $html);
});

test('get subject returns correct format', function (): void {
    $view = new CancellationConfirmation($this->order);

    expect($view->getSubject())->toContain('TEST-2024-001');
});

test('get subject returns preview when no order number', function (): void {
    // Create an order without order_number (in-memory only, don't save to DB)
    $order = new Order();
    $order->order_number = null;
    $order->setRelation('language', $this->order->language);

    $view = new CancellationConfirmation($order);

    expect($view->getSubject())->toContain(__('Preview'));
});
