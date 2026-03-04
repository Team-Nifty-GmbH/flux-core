<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Commission;
use FluxErp\Models\CommissionRate;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use FluxErp\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $dbTenant = Tenant::factory()->create();

    $language = Language::factory()->create();
    $this->agent = User::factory()->create([
        'language_id' => $language->id,
    ]);
    $this->agent->tenants()->attach($dbTenant->id);

    $contact = Contact::factory()->create([
        'tenant_id' => $dbTenant->id,
    ]);

    $currency = Currency::factory()->create();
    $priceList = PriceList::factory()->create();
    $paymentType = PaymentType::factory()->create();
    $language = Language::factory()->create();
    $orderType = OrderType::factory()->create([
        'tenant_id' => $dbTenant->id,
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $addressInvoice = Address::factory()->create([
        'tenant_id' => $dbTenant->id,
        'contact_id' => $contact->id,
    ]);

    $addressDelivery = Address::factory()->create([
        'tenant_id' => $dbTenant->id,
        'contact_id' => $contact->id,
    ]);

    $this->order = Order::factory()->create([
        'tenant_id' => $dbTenant->id,
        'contact_id' => $contact->id,
        'currency_id' => $currency->id,
        'address_invoice_id' => $addressInvoice->id,
        'address_delivery_id' => $addressDelivery->id,
        'price_list_id' => $priceList->id,
        'payment_type_id' => $paymentType->id,
        'language_id' => $language->id,
        'order_type_id' => $orderType->id,
    ]);

    $this->orderPosition = OrderPosition::factory()->create([
        'order_id' => $this->order->id,
        'tenant_id' => $dbTenant->id,
        'is_free_text' => false,
    ]);

    CommissionRate::factory()->create([
        'user_id' => $this->agent->id,
    ]);

    $this->commissions = Commission::factory()->count(3)->create([
        'user_id' => $this->agent->id,
        'order_position_id' => $this->orderPosition->id,
        'commission_rate' => [
            'commission_rate' => 0.05,
            'rate_type' => 'percentage',
        ],
    ]);

    $this->user->tenants()->attach($dbTenant->id);

    $this->permissions = [
        'show' => Permission::findOrCreate('api.commissions.{id}.get'),
        'index' => Permission::findOrCreate('api.commissions.get'),
        'create' => Permission::findOrCreate('api.commissions.post'),
        'update' => Permission::findOrCreate('api.commissions.put'),
        'delete' => Permission::findOrCreate('api.commissions.{id}.delete'),
    ];
});

test('commission avatar url method', function (): void {
    $commission = $this->commissions[0];

    $avatarUrl = $commission->getAvatarUrl();

    expect($avatarUrl)->toBeNull();
});

test('commission credit note relationship', function (): void {
    $creditNotePosition = OrderPosition::factory()->create([
        'order_id' => $this->order->id,
        'tenant_id' => $this->dbTenant->id,
    ]);

    $commission = Commission::factory()->create([
        'user_id' => $this->agent->id,
        'order_position_id' => $this->orderPosition->id,
        'credit_note_order_position_id' => $creditNotePosition->id,
    ]);

    expect($commission->creditNoteOrderPosition)->toBeInstanceOf(OrderPosition::class);
    expect($commission->creditNoteOrderPosition->id)->toEqual($creditNotePosition->id);
});

test('commission description method', function (): void {
    $commission = $this->commissions[0];

    $description = $commission->getDescription();

    expect($description)->toBeString();
    $this->assertStringContainsString('5%', $description);
});

test('commission rate casting', function (): void {
    $commission = $this->commissions[0];

    expect($commission->commission_rate)->toBeArray();
    expect($commission->commission_rate)->toHaveKey('commission_rate');
    expect($commission->commission_rate)->toHaveKey('rate_type');
});

test('commission relationships', function (): void {
    $commission = $this->commissions[0];

    expect($commission->user)->toBeInstanceOf(User::class);
    expect($commission->user->id)->toEqual($this->agent->id);

    expect($commission->orderPosition)->toBeInstanceOf(OrderPosition::class);
    expect($commission->orderPosition->id)->toEqual($this->orderPosition->id);
});

test('create commission', function (): void {
    $commission = [
        'user_id' => $this->agent->id,
        'order_position_id' => $this->orderPosition->id,
        'commission_rate' => 0.08,
        'total_net_price' => 100.00,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/commissions', $commission);
    $response->assertCreated();

    $responseCommission = json_decode($response->getContent())->data;
    $dbCommission = Commission::query()
        ->whereKey($responseCommission->id)
        ->first();

    expect($dbCommission)->not->toBeEmpty();
    expect($dbCommission->user_id)->toEqual($commission['user_id']);
    expect($dbCommission->order_position_id)->toEqual($commission['order_position_id']);
    expect($dbCommission->commission_rate['commission_rate'])->toEqual($commission['commission_rate']);
    expect($dbCommission->total_net_price)->toEqual($commission['total_net_price']);
    expect($this->user->is($dbCommission->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbCommission->getUpdatedBy()))->toBeTrue();
});

test('create commission applies order discount to total net price', function (): void {
    // OrderPosition has total_net_price of 1000 (after position discounts)
    $this->orderPosition->update(['total_net_price' => 1000]);

    // Set order-level discount (Kopfrabatt) of 10%
    // total_base_discounted_net_price = price AFTER position discounts, BEFORE order discount
    // total_net_price = final price AFTER all discounts
    $this->order->update([
        'total_base_discounted_net_price' => 1000,
        'total_net_price' => 900, // 1000 - 10% = 900
    ]);

    $commissionRate = CommissionRate::factory()->create([
        'user_id' => $this->agent->id,
        'commission_rate' => 0.05, // 5%
    ]);

    $result = FluxErp\Actions\Commission\CreateCommission::make([
        'user_id' => $this->agent->id,
        'order_position_id' => $this->orderPosition->id,
        'commission_rate_id' => $commissionRate->id,
    ])
        ->validate()
        ->execute();

    // Commission should be calculated on discounted price: 1000 * (1 - 0.10) * 0.05 = 900 * 0.05 = 45
    expect(bccomp($result->total_net_price, '900', 2))->toBe(0);
    expect(bccomp($result->commission, '45', 2))->toBe(0);
});

test('create commission without order discount uses full total net price', function (): void {
    // OrderPosition has total_net_price of 1000
    $this->orderPosition->update(['total_net_price' => 1000]);

    // No order-level discount - both values are the same
    $this->order->update([
        'total_base_discounted_net_price' => 1000,
        'total_net_price' => 1000, // No discount applied
    ]);

    $commissionRate = CommissionRate::factory()->create([
        'user_id' => $this->agent->id,
        'commission_rate' => 0.05, // 5%
    ]);

    $result = FluxErp\Actions\Commission\CreateCommission::make([
        'user_id' => $this->agent->id,
        'order_position_id' => $this->orderPosition->id,
        'commission_rate_id' => $commissionRate->id,
    ])
        ->validate()
        ->execute();

    // Commission should be calculated on full price: 1000 * 0.05 = 50
    expect(bccomp($result->total_net_price, '1000', 2))->toBe(0);
    expect(bccomp($result->commission, '50', 2))->toBe(0);
});

test('create commission validation fails', function (): void {
    $commission = [
        'user_id' => 999999,
        'order_position_id' => 999999,
        'total_net_price' => -100.00,
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->post('/api/commissions', $commission);
    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'user_id',
        'order_position_id',
    ]);
});

test('delete commission', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->delete('/api/commissions/' . $this->commissions[0]->id);
    $response->assertNoContent();

    $commission = $this->commissions[0]->fresh();
    expect($commission->deleted_at)->not->toBeNull();
    expect($this->user->is($commission->getDeletedBy()))->toBeTrue();
});

test('delete commission not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->delete('/api/commissions/' . (Commission::max('id') + 1));
    $response->assertNotFound();
});

test('get commission', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/commissions/' . $this->commissions[0]->id);
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonCommission = $json->data;

    expect($jsonCommission)->not->toBeEmpty();
    expect($jsonCommission->id)->toEqual($this->commissions[0]->id);
    expect($jsonCommission->user_id)->toEqual($this->commissions[0]->user_id);
    expect($jsonCommission->order_position_id)->toEqual($this->commissions[0]->order_position_id);
    expect((array) $jsonCommission->commission_rate)->toEqual($this->commissions[0]->commission_rate);
    expect($jsonCommission->total_net_price)->toEqual($this->commissions[0]->total_net_price);
    expect($jsonCommission->commission)->toEqual($this->commissions[0]->commission);
});

test('get commission not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/commissions/' . (Commission::max('id') + 1));
    $response->assertNotFound();
});

test('get commissions', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->get('/api/commissions');
    $response->assertOk();

    $json = json_decode($response->getContent());
    $jsonCommissions = collect($json->data->data);

    expect(count($jsonCommissions))->toBeGreaterThanOrEqual(3);

    foreach ($this->commissions as $commission) {
        $jsonCommissions->contains(function ($jsonCommission) use ($commission) {
            return $jsonCommission->id === $commission->id &&
                $jsonCommission->user_id === $commission->user_id &&
                $jsonCommission->order_position_id === $commission->order_position_id &&
                $jsonCommission->commission_rate === $commission->commission_rate &&
                $jsonCommission->total_net_price === $commission->total_net_price &&
                $jsonCommission->commission === $commission->commission;
        });
    }
});

test('update commission', function (): void {
    $commission = [
        'id' => $this->commissions[0]->id,
        'commission' => 15.00,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->put('/api/commissions', $commission);
    $response->assertOk();

    $responseCommission = json_decode($response->getContent())->data;
    $dbCommission = Commission::query()
        ->whereKey($responseCommission->id)
        ->first();

    expect($dbCommission)->not->toBeEmpty();
    expect($dbCommission->id)->toEqual($commission['id']);
    expect($dbCommission->commission)->toEqual($commission['commission']);
    expect($this->user->is($dbCommission->getUpdatedBy()))->toBeTrue();
});

test('update commission maximum', function (): void {
    $commission = [
        'id' => $this->commissions[1]->id,
        'commission' => 36.00,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->put('/api/commissions', $commission);
    $response->assertOk();

    $responseCommission = json_decode($response->getContent())->data;
    $dbCommission = Commission::query()
        ->whereKey($responseCommission->id)
        ->first();

    expect($dbCommission)->not->toBeEmpty();
    expect($dbCommission->commission)->toEqual($commission['commission']);
    expect($this->user->is($dbCommission->getUpdatedBy()))->toBeTrue();
});

test('create commission credit notes with nested id array structure', function (): void {
    // Create a default PaymentType and attach to tenant
    $paymentType = PaymentType::factory()->create(['is_default' => true]);
    $paymentType->tenants()->attach($this->order->tenant_id);

    // Create a contact with address for the agent
    $agentContact = Contact::factory()->create([
        'tenant_id' => $this->order->tenant_id,
        'payment_type_id' => $paymentType->id,
    ]);
    $agentAddress = Address::factory()->create([
        'tenant_id' => $this->order->tenant_id,
        'contact_id' => $agentContact->id,
        'is_main_address' => true,
    ]);
    $agentContact->update(['invoice_address_id' => $agentAddress->id]);
    $this->agent->update(['contact_id' => $agentContact->id]);

    // Create a refund order type
    OrderType::factory()->create([
        'tenant_id' => $this->order->tenant_id,
        'order_type_enum' => OrderTypeEnum::Refund,
    ]);

    // Ensure order has invoice data (required for Commission::getLabel())
    $this->order->update([
        'invoice_date' => now(),
        'invoice_number' => 'INV-TEST-001',
    ]);

    // Ensure commissions have order_id set (required for withWhereHas('order', ...) in the action)
    foreach ($this->commissions as $commission) {
        $commission->update(['order_id' => $this->order->id]);
    }

    // Get commission IDs in the format that CommissionList produces: [['id' => 1], ['id' => 2]]
    $commissionIds = $this->commissions->map(fn ($commission) => ['id' => $commission->id])->toArray();

    $result = FluxErp\Actions\Commission\CreateCommissionCreditNotes::make([
        'commissions' => $commissionIds,
    ])
        ->validate()
        ->execute();

    expect($result)->toBeInstanceOf(FluxErp\Support\Collection\OrderCollection::class);
    expect($result)->toHaveCount(1);

    // Verify commissions are linked to credit note order positions
    foreach ($this->commissions as $commission) {
        $commission->refresh();
        expect($commission->credit_note_order_position_id)->not->toBeNull();
    }
});
