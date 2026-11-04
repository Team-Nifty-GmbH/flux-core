<?php

use FluxErp\Actions\Printing;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;

test('stores the reminder pdf on the order and links it as media of the reminder', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create([
        'contact_id' => $contact->getKey(),
        'is_main_address' => true,
        'is_invoice_address' => true,
    ]);
    $order = Order::factory()->create([
        'order_type_id' => OrderType::factory()->create([
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ])->getKey(),
        'address_invoice_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached($this->dbTenant, relationship: 'tenants')
            ->create(['is_direct_debit' => false])
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'is_locked' => true,
        'invoice_number' => 'INV-2026-200',
    ]);
    $reminder = PaymentReminder::factory()->create([
        'order_id' => $order->getKey(),
        'reminder_level' => 1,
    ]);

    $media = Printing::make([
        'model_type' => $reminder->getMorphClass(),
        'model_id' => $reminder->getKey(),
        'view' => 'payment-reminder',
    ])
        ->validate()
        ->execute()
        ->attachToModel($order);

    expect($media)->not->toBeNull()
        ->and($media->model_type)->toBe($order->getMorphClass())
        ->and($media->model_id)->toBe($order->getKey())
        ->and($media->collection_name)->toBe('payment-reminders')
        ->and($reminder->fresh()->media_id)->toBe($media->getKey());
});
