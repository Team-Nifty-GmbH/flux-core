<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create(['order_type_enum' => OrderTypeEnum::Order]);

    $this->order = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_id' => $orderType->getKey(),
        'address_invoice_id' => $address->getKey(),
        'address_delivery_id' => $address->getKey(),
        'contact_id' => $contact->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'is_locked' => false,
    ]);
});

test('free text positions do not consume a slug number', function (): void {
    $first = OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'sort_number' => 1,
        'is_free_text' => false,
    ]);
    $freeText = OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'sort_number' => 2,
        'is_free_text' => true,
    ]);
    $third = OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'sort_number' => 3,
        'is_free_text' => false,
    ]);

    $this->order->recalculateOrderPositionSlugPositions();

    expect($first->fresh()->slug_position)->toBe('1')
        ->and(DB::table('order_positions')->where('id', $freeText->getKey())->value('slug_position'))->toBeNull()
        ->and($third->fresh()->slug_position)->toBe('2');
});

test('free text children do not consume a slug number under their parent', function (): void {
    $parent = OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'sort_number' => 1,
        'is_free_text' => false,
    ]);
    $childOne = OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'parent_id' => $parent->getKey(),
        'sort_number' => 1,
        'is_free_text' => false,
    ]);
    $freeTextChild = OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'parent_id' => $parent->getKey(),
        'sort_number' => 2,
        'is_free_text' => true,
    ]);
    $childThree = OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'parent_id' => $parent->getKey(),
        'sort_number' => 3,
        'is_free_text' => false,
    ]);

    $this->order->recalculateOrderPositionSlugPositions();

    expect($parent->fresh()->slug_position)->toBe('1')
        ->and($childOne->fresh()->slug_position)->toBe('1.1')
        ->and(DB::table('order_positions')->where('id', $freeTextChild->getKey())->value('slug_position'))->toBeNull()
        ->and($childThree->fresh()->slug_position)->toBe('1.2');
});

test('order with only free text positions assigns no slug numbers', function (): void {
    $freeText = OrderPosition::factory()->create([
        'order_id' => $this->order->getKey(),
        'tenant_id' => $this->dbTenant->getKey(),
        'sort_number' => 1,
        'is_free_text' => true,
    ]);

    $this->order->recalculateOrderPositionSlugPositions();

    expect(DB::table('order_positions')->where('id', $freeText->getKey())->value('slug_position'))->toBeNull();
});
