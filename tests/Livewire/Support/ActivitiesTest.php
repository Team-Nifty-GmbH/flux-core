<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Order\Activities;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('activities handle null attribute_changes without error', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order, 'is_active' => true]);
    $paymentType = PaymentType::factory()->hasAttached($this->dbTenant, relationship: 'tenants')->create();

    $order = Order::factory()->create([
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
    ]);

    // Create a real activity log entry with null attribute_changes
    DB::table('activity_log')->insert([
        'log_name' => 'default',
        'description' => 'updated',
        'subject_type' => morph_alias(Order::class),
        'subject_id' => $order->getKey(),
        'causer_type' => morph_alias(User::class),
        'causer_id' => $this->user->getKey(),
        'event' => 'updated',
        'attribute_changes' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(Activities::class, ['modelId' => $order->getKey()])
        ->assertSuccessful();
});
