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
