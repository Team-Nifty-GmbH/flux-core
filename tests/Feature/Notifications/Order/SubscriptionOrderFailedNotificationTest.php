<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use FluxErp\Models\User;
use FluxErp\Notifications\Order\SubscriptionOrderFailedNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->currency = Currency::factory()->create(['is_default' => true]);
    $this->language = Language::factory()->create(['is_default' => true]);
    $this->priceList = PriceList::factory()->create(['is_default' => true]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create(['is_default' => true]);

    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create();

    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);

    $this->subscriptionOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::Subscription,
            'is_active' => true,
        ]);

    $this->targetOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ]);

    $this->creator = User::factory()->create(['is_active' => true]);

    $this->subscriptionOrder = Order::factory()->create([
        'tenant_id' => $this->tenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'address_invoice_id' => $this->address->getKey(),
        'order_type_id' => $this->subscriptionOrderType->getKey(),
        'currency_id' => $this->currency->getKey(),
        'language_id' => $this->language->getKey(),
        'price_list_id' => $this->priceList->getKey(),
        'payment_type_id' => $this->paymentType->getKey(),
        'parent_id' => null,
    ]);

    // The HasUserModification trait overwrites `created_by` from auth()->user() during model save,
    // so we set it via raw query after creation to ensure the order has a known creator.
    DB::table('orders')
        ->where('id', $this->subscriptionOrder->getKey())
        ->update([
            'created_by' => $this->creator->getMorphClass() . ':' . $this->creator->getKey(),
        ]);
});

test('subscription order failure notifies the order creator', function (): void {
    Notification::fake();

    $otherTenant = Tenant::factory()->create();
    $otherContact = Contact::factory()
        ->hasAttached($otherTenant, relationship: 'tenants')
        ->create();

    DB::table('orders')
        ->where('id', $this->subscriptionOrder->getKey())
        ->update(['contact_id' => $otherContact->getKey()]);

    $processor = app(ProcessSubscriptionOrder::class);

    try {
        $processor(
            orderId: $this->subscriptionOrder->getKey(),
            orderTypeId: $this->targetOrderType->getKey(),
        );
    } catch (ValidationException) {
        // expected
    }

    Notification::assertSentTo(
        $this->creator,
        SubscriptionOrderFailedNotification::class,
        fn (SubscriptionOrderFailedNotification $notification): bool => $notification->event->order->is($this->subscriptionOrder)
            && $notification->event->exceptionClass === ValidationException::class,
    );
    Notification::assertSentToTimes($this->creator, SubscriptionOrderFailedNotification::class, 1);
});

test('subscription order failure does not notify when order has no creator', function (): void {
    Notification::fake();

    DB::table('orders')
        ->where('id', $this->subscriptionOrder->getKey())
        ->update(['created_by' => null]);

    $otherTenant = Tenant::factory()->create();
    $otherContact = Contact::factory()
        ->hasAttached($otherTenant, relationship: 'tenants')
        ->create();

    DB::table('orders')
        ->where('id', $this->subscriptionOrder->getKey())
        ->update(['contact_id' => $otherContact->getKey()]);

    $processor = app(ProcessSubscriptionOrder::class);

    try {
        $processor(
            orderId: $this->subscriptionOrder->getKey(),
            orderTypeId: $this->targetOrderType->getKey(),
        );
    } catch (ValidationException) {
        // expected
    }

    Notification::assertNothingSent();
});
