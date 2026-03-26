<?php

use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\Tenant;
use FluxErp\Models\VatRate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    Storage::fake('public');

    $this->tenants = Tenant::factory()->count(2)->create();

    $this->paymentTypes = PaymentType::factory()
        ->count(2)
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create([
            'is_active' => true,
            'is_purchase' => true,
        ]);

    $this->contacts = Contact::factory()->count(2)
        ->has(Address::factory()->set('tenant_id', $this->dbTenant->getKey()))
        ->for(PriceList::factory()->state(['is_default' => true]))
        ->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'payment_type_id' => $this->paymentTypes->random()->id,
            'payment_target_days' => 0,
            'discount_days' => 0,
        ]);

    $this->currencies = Currency::factory()->count(2)->create();
    Currency::query()->first()->update(['is_default' => true]);

    $language = Language::factory()->create();

    $this->orderTypes = OrderType::factory()->count(2)->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Purchase,
    ]);

    $vatRates = VatRate::factory()->count(3)->create();
    $this->purchaseInvoices = PurchaseInvoice::factory()
        ->has(PurchaseInvoicePosition::factory()->count(2)->set('vat_rate_id', $vatRates->random()->id))
        ->count(3)
        ->afterCreating(function (PurchaseInvoice $purchaseInvoice): void {
            $purchaseInvoice->addMedia(UploadedFile::fake()->image($purchaseInvoice->invoice_number . '.jpeg'))
                ->toMediaCollection('purchase_invoice');

            $purchaseInvoice->update([
                'total_gross_price' => bcround($purchaseInvoice->calculateTotalGrossPrice(), 2),
            ]);
        })
        ->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'order_type_id' => $this->orderTypes->random()->id,
            'payment_type_id' => $this->paymentTypes->random()->id,
            'currency_id' => $this->currencies->random()->id,
            'contact_id' => $this->contacts->random()->id,
        ]);

    $this->order = Order::factory()->create([
        'tenant_id' => $this->tenants[0]->id,
        'currency_id' => $this->currencies->random()->id,
        'order_type_id' => $this->orderTypes[0]->id,
        'payment_type_id' => $this->paymentTypes[0]->id,
        'contact_id' => $this->contacts->random()->id,
        'address_invoice_id' => $this->contacts->random()->addresses->first()->id,
        'language_id' => $language->id,
        'is_locked' => true,
    ]);

    $this->order->invoice_number = Str::uuid()->toString();
    $this->order->save();

    $this->user->tenants()->attach($this->tenants->pluck('id')->toArray());

    $this->permissions = [
        'show' => Permission::findOrCreate('api.purchase-invoices.{id}.get'),
        'index' => Permission::findOrCreate('api.purchase-invoices.get'),
        'create' => Permission::findOrCreate('api.purchase-invoices.post'),
        'update' => Permission::findOrCreate('api.purchase-invoices.put'),
        'delete' => Permission::findOrCreate('api.purchase-invoices.{id}.delete'),
        'finish' => Permission::findOrCreate('api.purchase-invoices.finish'),
    ];
});

test('can create purchase invoice with all fields', function (): void {
    $ledgerAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);
    $product = Product::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $vatRate = VatRate::factory()->create();

    $purchaseInvoice = [
        'uuid' => Str::uuid()->toString(),
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contacts->random()->id,
        'currency_id' => $this->currencies->random()->id,
        'order_type_id' => $this->orderTypes->random()->id,
        'payment_type_id' => $this->paymentTypes->random()->id,
        'invoice_date' => Carbon::yesterday()->toDateString(),
        'payment_target_date' => Carbon::now()->addDays(30)->toDateString(),
        'payment_discount_target_date' => Carbon::now()->addDays(10)->toDateString(),
        'payment_discount_percent' => 0.03,
        'invoice_number' => Str::random(),
        'is_net' => false,
        'media' => UploadedFile::fake()->image('test_purchase_invoice.jpeg'),
        'purchase_invoice_positions' => [
            [
                'ledger_account_id' => $ledgerAccount->id,
                'product_id' => $product->id,
                'vat_rate_id' => $vatRate->id,
                'name' => Str::random(),
                'amount' => $amount = rand(1, 100),
                'unit_price' => $unitPrice = bcdiv(rand(0, 10000), 100),
                'total_price' => bcmul($amount, $unitPrice),
            ],
        ],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/purchase-invoices', $purchaseInvoice);

    $response->assertCreated();

    $dbPurchaseInvoice = PurchaseInvoice::query()
        ->whereKey(json_decode($response->getContent())->data->id)
        ->first();

    expect($dbPurchaseInvoice)
        ->uuid->toEqual($purchaseInvoice['uuid'])
        ->tenant_id->toEqual($purchaseInvoice['tenant_id'])
        ->contact_id->toEqual($purchaseInvoice['contact_id'])
        ->currency_id->toEqual($purchaseInvoice['currency_id'])
        ->media_id->not->toBeNull()
        ->order_id->toBeNull()
        ->order_type_id->toEqual($purchaseInvoice['order_type_id'])
        ->payment_type_id->toEqual($purchaseInvoice['payment_type_id'])
        ->payment_discount_percent->toEqual($purchaseInvoice['payment_discount_percent'])
        ->invoice_number->toEqual($purchaseInvoice['invoice_number'])
        ->is_net->toEqual($purchaseInvoice['is_net']);

    expect($dbPurchaseInvoice->invoice_date->toDateString())->toEqual($purchaseInvoice['invoice_date']);
    expect($dbPurchaseInvoice->payment_target_date->toDateString())->toEqual($purchaseInvoice['payment_target_date']);
    expect($dbPurchaseInvoice->payment_discount_target_date->toDateString())->toEqual($purchaseInvoice['payment_discount_target_date']);

    expect($dbPurchaseInvoice->getCreatedBy()->is($this->user))->toBeTrue();
    expect($dbPurchaseInvoice->getUpdatedBy()->is($this->user))->toBeTrue();

    $position = $dbPurchaseInvoice->purchaseInvoicePositions->first();

    expect($position)
        ->ledger_account_id->toEqual($purchaseInvoice['purchase_invoice_positions'][0]['ledger_account_id'])
        ->product_id->toEqual($purchaseInvoice['purchase_invoice_positions'][0]['product_id'])
        ->vat_rate_id->toEqual($purchaseInvoice['purchase_invoice_positions'][0]['vat_rate_id'])
        ->name->toEqual($purchaseInvoice['purchase_invoice_positions'][0]['name'])
        ->amount->toEqual($purchaseInvoice['purchase_invoice_positions'][0]['amount']);

    expect(bcround($position->unit_price, 2))->toEqual(bcround($purchaseInvoice['purchase_invoice_positions'][0]['unit_price'], 2));
    expect(bcround($position->total_price, 2))->toEqual(bcround($purchaseInvoice['purchase_invoice_positions'][0]['total_price'], 2));
});

test('can create purchase invoice with minimum fields', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/purchase-invoices', [
        'media' => UploadedFile::fake()->image('test_purchase_invoice.jpeg'),
    ]);

    $response->assertCreated();

    $dbPurchaseInvoice = PurchaseInvoice::query()
        ->whereKey(json_decode($response->getContent())->data->id)
        ->first();

    expect($dbPurchaseInvoice)
        ->tenant_id->toEqual(Tenant::default()?->id)
        ->contact_id->toBeNull()
        ->currency_id->toBeNull()
        ->media_id->not->toBeNull()
        ->order_id->toBeNull()
        ->order_type_id->toBeNull()
        ->payment_type_id->toBeNull()
        ->invoice_number->toBeNull()
        ->is_net->toBeFalse()
        ->purchaseInvoicePositions->toBeEmpty();

    expect($dbPurchaseInvoice->invoice_date->toDateString())->toEqual(Carbon::now()->toDateString());
    expect($dbPurchaseInvoice->getCreatedBy()->is($this->user))->toBeTrue();
    expect($dbPurchaseInvoice->getUpdatedBy()->is($this->user))->toBeTrue();
});

test('validates required fields on creation', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/purchase-invoices', [
        'tenant_id' => $this->dbTenant->getKey(),
        'purchase_invoice_positions' => [],
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['media'])
        ->assertJsonMissingValidationErrors([
            'purchase_invoice_positions.0.amount',
            'purchase_invoice_positions.0.unit_price',
            'purchase_invoice_positions.0.total_price',
        ]);
});

test('validates position fields', function (): void {
    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/purchase-invoices', [
        'media' => UploadedFile::fake()->image('test_purchase_invoice.jpeg'),
        'purchase_invoice_positions' => [
            [
                'amount' => 'test',
                'unit_price' => 'test',
                'total_price' => 'test',
            ],
        ],
    ]);

    $response->assertUnprocessable()
        ->assertJsonMissingValidationErrors(['media'])
        ->assertJsonValidationErrors([
            'purchase_invoice_positions.0.amount',
            'purchase_invoice_positions.0.unit_price',
            'purchase_invoice_positions.0.total_price',
        ]);
});

test('can delete purchase invoice', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/purchase-invoices/' . $this->purchaseInvoices[1]->id);

    $response->assertNoContent();

    $purchaseInvoice = $this->purchaseInvoices[1]->fresh();

    expect($purchaseInvoice->deleted_at)->not->toBeNull();
    expect($purchaseInvoice->getDeletedBy()->is($this->user))->toBeTrue();
});

test('returns 404 when deleting non-existent purchase invoice', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/purchase-invoices/' . ++$this->purchaseInvoices[2]->id);

    $response->assertNotFound();
});

test('converts purchase invoice to order with payment fields', function (): void {
    ContactBankConnection::factory()->create([
        'contact_id' => $this->purchaseInvoices[0]->contact_id,
    ]);

    $this->purchaseInvoices[0]->update([
        'invoice_date' => Carbon::now()->toDateString(),
        'payment_target_date' => Carbon::now()->addDays(30)->toDateString(),
        'payment_discount_target_date' => Carbon::now()->addDays(10)->toDateString(),
        'payment_discount_percent' => 0.02,
    ]);

    $this->user->givePermissionTo($this->permissions['finish']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/purchase-invoices/finish', [
        'id' => $this->purchaseInvoices[0]->id,
    ]);

    $response->assertCreated();

    $dbOrder = Order::query()->whereKey(json_decode($response->getContent())->data->id)->first();
    $dbPurchaseInvoice = $this->purchaseInvoices[0]->fresh();

    expect($dbOrder)
        ->tenant_id->toEqual($dbPurchaseInvoice->tenant_id)
        ->contact_id->toEqual($dbPurchaseInvoice->contact_id)
        ->currency_id->toEqual($dbPurchaseInvoice->currency_id)
        ->order_type_id->toEqual($dbPurchaseInvoice->order_type_id)
        ->payment_type_id->toEqual($dbPurchaseInvoice->payment_type_id)
        ->invoice_number->toEqual($dbPurchaseInvoice->invoice_number)
        ->payment_target->toEqual(30)
        ->payment_discount_target->toEqual(10)
        ->payment_discount_percent->toEqual($dbPurchaseInvoice->payment_discount_percent);

    expect($dbOrder->getFirstMedia('invoice')->id)->toEqual($dbPurchaseInvoice->media_id);
    expect($dbPurchaseInvoice->order_id)->toEqual($dbOrder->id);
    expect($dbPurchaseInvoice->total_gross_price)->toEqual($this->purchaseInvoices[0]->total_gross_price);

    expect($dbOrder->invoice_date->toDateString())->toEqual($dbPurchaseInvoice->invoice_date->toDateString());
    expect($dbOrder->payment_target_date->toDateString())->toEqual($dbPurchaseInvoice->payment_target_date->toDateString());
    expect($dbOrder->payment_discount_target_date->toDateString())->toEqual($dbPurchaseInvoice->payment_discount_target_date->toDateString());
});

test('validates required fields when finishing purchase invoice', function (): void {
    $this->purchaseInvoices[1]->update([
        'tenant_id' => null,
        'contact_id' => null,
        'order_type_id' => null,
        'invoice_number' => null,
    ]);

    $this->user->givePermissionTo($this->permissions['finish']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/purchase-invoices/finish', [
        'id' => $this->purchaseInvoices[1]->id,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'tenant_id',
            'contact_id',
            'order_type_id',
            'invoice_number',
        ]);
});

test('can get single purchase invoice', function (): void {
    $this->purchaseInvoices[0]->refresh();

    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/purchase-invoices/' . $this->purchaseInvoices[0]->id);

    $response->assertOk();

    $purchaseInvoice = json_decode($response->getContent())->data;

    expect($purchaseInvoice)
        ->id->toEqual($this->purchaseInvoices[0]->id)
        ->tenant_id->toEqual($this->purchaseInvoices[0]->tenant_id)
        ->contact_id->toEqual($this->purchaseInvoices[0]->contact_id)
        ->currency_id->toEqual($this->purchaseInvoices[0]->currency_id)
        ->media_id->toEqual($this->purchaseInvoices[0]->media_id)
        ->order_id->toEqual($this->purchaseInvoices[0]->order_id)
        ->order_type_id->toEqual($this->purchaseInvoices[0]->order_type_id)
        ->payment_type_id->toEqual($this->purchaseInvoices[0]->payment_type_id)
        ->invoice_number->toEqual($this->purchaseInvoices[0]->invoice_number)
        ->hash->toEqual($this->purchaseInvoices[0]->hash)
        ->is_net->toEqual($this->purchaseInvoices[0]->is_net);

    expect(Carbon::parse($purchaseInvoice->invoice_date)->toDateString())
        ->toEqual($this->purchaseInvoices[0]->invoice_date->toDateString());
    expect(Carbon::parse($purchaseInvoice->created_at))
        ->toEqual(Carbon::parse($this->purchaseInvoices[0]->created_at));
    expect(Carbon::parse($purchaseInvoice->updated_at))
        ->toEqual(Carbon::parse($this->purchaseInvoices[0]->updated_at));
});

test('returns 404 when getting non-existent purchase invoice', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/purchase-invoices/' . ++$this->purchaseInvoices[2]->id);

    $response->assertNotFound();
});

test('can list purchase invoices', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/purchase-invoices');

    $response->assertOk();

    $purchaseInvoices = json_decode($response->getContent())->data->data;

    expect($purchaseInvoices)
        ->not->toBeEmpty()
        ->and(count($purchaseInvoices))->toBeGreaterThanOrEqual(3);

    expect($purchaseInvoices[0])->toHaveProperty('id');

    $referencePurchaseInvoice = PurchaseInvoice::query()
        ->whereKey($purchaseInvoices[0]->id)
        ->first();

    expect($referencePurchaseInvoice)->not->toBeNull();

    expect($this->purchaseInvoices[0])
        ->id->toEqual($referencePurchaseInvoice->id)
        ->tenant_id->toEqual($referencePurchaseInvoice->tenant_id)
        ->contact_id->toEqual($referencePurchaseInvoice->contact_id)
        ->currency_id->toEqual($referencePurchaseInvoice->currency_id)
        ->media_id->toEqual($referencePurchaseInvoice->media_id)
        ->order_id->toEqual($referencePurchaseInvoice->order_id)
        ->order_type_id->toEqual($referencePurchaseInvoice->order_type_id)
        ->payment_type_id->toEqual($referencePurchaseInvoice->payment_type_id)
        ->invoice_number->toEqual($referencePurchaseInvoice->invoice_number)
        ->hash->toEqual($referencePurchaseInvoice->hash)
        ->is_net->toEqual($referencePurchaseInvoice->is_net);

    expect($this->purchaseInvoices[0]->invoice_date->toDateString())
        ->toEqual($referencePurchaseInvoice->invoice_date->toDateString());
    expect(Carbon::parse($purchaseInvoices[0]->created_at))
        ->toEqual(Carbon::parse($referencePurchaseInvoice->created_at));
    expect(Carbon::parse($purchaseInvoices[0]->updated_at))
        ->toEqual(Carbon::parse($referencePurchaseInvoice->updated_at));
});

test('can update purchase invoice', function (): void {
    $purchaseInvoice = [
        'id' => $this->purchaseInvoices[0]->id,
        'contact_id' => $this->contacts->random()->id,
        'currency_id' => $this->currencies->random()->id,
        'order_type_id' => $this->orderTypes->random()->id,
        'payment_type_id' => $this->paymentTypes->random()->id,
        'invoice_date' => Carbon::now()->toDateString(),
        'invoice_number' => Str::random(),
        'purchase_invoice_positions' => [
            [
                'amount' => rand(0, 100),
                'unit_price' => rand(0, 10000) / 100,
                'total_price' => rand(0, 10000) / 100,
            ],
        ],
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/purchase-invoices', $purchaseInvoice);

    $response->assertOk();

    $dbPurchaseInvoice = PurchaseInvoice::query()
        ->whereKey(json_decode($response->getContent())->data->id)
        ->first();

    expect($dbPurchaseInvoice)
        ->id->toEqual($purchaseInvoice['id'])
        ->uuid->toEqual($this->purchaseInvoices[0]->uuid)
        ->tenant_id->toEqual($this->purchaseInvoices[0]->tenant_id)
        ->contact_id->toEqual($purchaseInvoice['contact_id'])
        ->currency_id->toEqual($purchaseInvoice['currency_id'])
        ->media_id->toEqual($this->purchaseInvoices[0]->media_id)
        ->order_id->toEqual($this->purchaseInvoices[0]->order_id)
        ->order_type_id->toEqual($purchaseInvoice['order_type_id'])
        ->payment_type_id->toEqual($purchaseInvoice['payment_type_id'])
        ->invoice_number->toEqual($purchaseInvoice['invoice_number'])
        ->hash->toEqual($this->purchaseInvoices[0]->hash)
        ->is_net->toEqual($this->purchaseInvoices[0]->is_net)
        ->purchaseInvoicePositions->toHaveCount(count($purchaseInvoice['purchase_invoice_positions']));

    expect($dbPurchaseInvoice->invoice_date->toDateString())->toEqual($purchaseInvoice['invoice_date']);
    expect($dbPurchaseInvoice->getUpdatedBy()->is($this->user))->toBeTrue();
});

test('validates unique invoice number on update', function (): void {
    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/purchase-invoices', [
        'id' => $this->purchaseInvoices[0]->id,
        'invoice_number' => $this->order->invoice_number,
        'contact_id' => $this->order->contact_id,
        'tenant_id' => $this->order->tenant_id,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['invoice_number']);
});
