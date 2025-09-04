<?php

use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
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
use FluxErp\Models\VatRate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    Storage::fake('public');

    $this->clients = Client::factory()->count(2)->create();

    $this->paymentTypes = PaymentType::factory()
        ->count(2)
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create([
            'is_active' => true,
            'is_purchase' => true,
        ]);

    $this->contacts = Contact::factory()->count(2)
        ->has(Address::factory()->set('client_id', $this->dbClient->getKey()))
        ->for(PriceList::factory()->state(['is_default' => true]))
        ->create([
            'client_id' => $this->dbClient->getKey(),
            'payment_type_id' => $this->paymentTypes->random()->id,
            'payment_target_days' => 0,
            'discount_days' => 0,
        ]);

    $this->currencies = Currency::factory()->count(2)->create();
    Currency::query()->first()->update(['is_default' => true]);

    $language = Language::factory()->create();

    $this->orderTypes = OrderType::factory()->count(2)->create([
        'client_id' => $this->dbClient->getKey(),
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
            'client_id' => $this->dbClient->getKey(),
            'order_type_id' => $this->orderTypes->random()->id,
            'payment_type_id' => $this->paymentTypes->random()->id,
            'currency_id' => $this->currencies->random()->id,
            'contact_id' => $this->contacts->random()->id,
        ]);

    $this->order = Order::factory()->create([
        'client_id' => $this->clients[0]->id,
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

    $this->user->clients()->attach($this->clients->pluck('id')->toArray());

    $this->permissions = [
        'show' => Permission::findOrCreate('api.purchase-invoices.{id}.get'),
        'index' => Permission::findOrCreate('api.purchase-invoices.get'),
        'create' => Permission::findOrCreate('api.purchase-invoices.post'),
        'update' => Permission::findOrCreate('api.purchase-invoices.put'),
        'delete' => Permission::findOrCreate('api.purchase-invoices.{id}.delete'),
        'finish' => Permission::findOrCreate('api.purchase-invoices.finish'),
    ];
});

test('create purchase invoice maximum', function (): void {
    $ledgerAccount = LedgerAccount::factory()->create([
        'client_id' => $this->dbClient->getKey(),
    ]);
    $product = Product::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create();
    $vatRate = VatRate::factory()->create();

    $purchaseInvoice = [
        'uuid' => Str::uuid()->toString(),
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $this->contacts->random()->id,
        'currency_id' => $this->currencies->random()->id,
        'order_type_id' => $this->orderTypes->random()->id,
        'payment_type_id' => $this->paymentTypes->random()->id,
        'invoice_date' => Carbon::yesterday()->toDateString(),
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

    $responsePurchaseInvoice = json_decode($response->getContent())->data;
    $dbPurchaseInvoice = PurchaseInvoice::query()
        ->whereKey($responsePurchaseInvoice->id)
        ->first();

    expect($dbPurchaseInvoice)->not->toBeEmpty();
    expect($dbPurchaseInvoice->uuid)->toEqual($purchaseInvoice['uuid']);
    expect($dbPurchaseInvoice->client_id)->toEqual($purchaseInvoice['client_id']);
    expect($dbPurchaseInvoice->contact_id)->toEqual($purchaseInvoice['contact_id']);
    expect($dbPurchaseInvoice->currency_id)->toEqual($purchaseInvoice['currency_id']);
    expect($dbPurchaseInvoice->media_id)->not->toBeNull();
    expect($dbPurchaseInvoice->order_id)->toBeNull();
    expect($dbPurchaseInvoice->order_type_id)->toEqual($purchaseInvoice['order_type_id']);
    expect($dbPurchaseInvoice->payment_type_id)->toEqual($purchaseInvoice['payment_type_id']);
    expect(Carbon::parse($dbPurchaseInvoice->invoice_date)->toDateString())->toEqual($purchaseInvoice['invoice_date']);
    expect($dbPurchaseInvoice->invoice_number)->toEqual($purchaseInvoice['invoice_number']);
    expect($dbPurchaseInvoice->is_net)->toEqual($purchaseInvoice['is_net']);
    expect($this->user->is($dbPurchaseInvoice->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbPurchaseInvoice->getUpdatedBy()))->toBeTrue();

    $dbPurchaseInvoicePositions = $dbPurchaseInvoice->purchaseInvoicePositions;
    expect($dbPurchaseInvoicePositions)->toHaveCount(count($purchaseInvoice['purchase_invoice_positions']));
    expect($dbPurchaseInvoicePositions[0]->ledger_account_id)->toEqual($purchaseInvoice['purchase_invoice_positions'][0]['ledger_account_id']);
    expect($dbPurchaseInvoicePositions[0]->product_id)->toEqual($purchaseInvoice['purchase_invoice_positions'][0]['product_id']);
    expect($dbPurchaseInvoicePositions[0]->vat_rate_id)->toEqual($purchaseInvoice['purchase_invoice_positions'][0]['vat_rate_id']);
    expect($dbPurchaseInvoicePositions[0]->name)->toEqual($purchaseInvoice['purchase_invoice_positions'][0]['name']);
    expect($dbPurchaseInvoicePositions[0]->amount)->toEqual($purchaseInvoice['purchase_invoice_positions'][0]['amount']);
    expect(bcround($dbPurchaseInvoicePositions[0]->unit_price, 2))->toEqual(bcround($purchaseInvoice['purchase_invoice_positions'][0]['unit_price'], 2));
    expect(bcround($dbPurchaseInvoicePositions[0]->total_price, 2))->toEqual(bcround($purchaseInvoice['purchase_invoice_positions'][0]['total_price'], 2));
});

test('create purchase invoice minimum', function (): void {
    $purchaseInvoice = [
        'media' => UploadedFile::fake()->image('test_purchase_invoice.jpeg'),
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/purchase-invoices', $purchaseInvoice);
    $response->assertCreated();

    $responsePurchaseInvoice = json_decode($response->getContent())->data;
    $dbPurchaseInvoice = PurchaseInvoice::query()
        ->whereKey($responsePurchaseInvoice->id)
        ->first();

    expect($dbPurchaseInvoice)->not->toBeEmpty();
    expect($dbPurchaseInvoice->client_id)->toEqual(Client::default()?->id);
    expect($dbPurchaseInvoice->contact_id)->toBeNull();
    expect($dbPurchaseInvoice->currency_id)->toBeNull();
    expect($dbPurchaseInvoice->media_id)->not->toBeNull();
    expect($dbPurchaseInvoice->order_id)->toBeNull();
    expect($dbPurchaseInvoice->order_type_id)->toBeNull();
    expect($dbPurchaseInvoice->payment_type_id)->toBeNull();
    expect(Carbon::parse($dbPurchaseInvoice->invoice_date)->toDateString())->toEqual(Carbon::now()->toDateString());
    expect($dbPurchaseInvoice->invoice_number)->toBeNull();
    expect($dbPurchaseInvoice->is_net)->toBeFalse();
    expect($this->user->is($dbPurchaseInvoice->getCreatedBy()))->toBeTrue();
    expect($this->user->is($dbPurchaseInvoice->getUpdatedBy()))->toBeTrue();
    expect($dbPurchaseInvoice->purchaseInvoicePositions)->toBeEmpty();
});

test('create purchase invoice validation fails', function (): void {
    $purchaseInvoice = [
        'client_id' => $this->dbClient->getKey(),
        'purchase_invoice_positions' => [],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/purchase-invoices', $purchaseInvoice);
    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'media',
    ]);

    $response->assertJsonMissingValidationErrors([
        'purchase_invoice_positions.0.amount',
        'purchase_invoice_positions.0.unit_price',
        'purchase_invoice_positions.0.total_price',
    ]);
});

test('create purchase invoice validation fails positions', function (): void {
    $purchaseInvoice = [
        'media' => UploadedFile::fake()->image('test_purchase_invoice.jpeg'),
        'purchase_invoice_positions' => [
            [
                'amount' => 'test',
                'unit_price' => 'test',
                'total_price' => 'test',
            ],
        ],
    ];

    $this->user->givePermissionTo($this->permissions['create']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/purchase-invoices', $purchaseInvoice);
    $response->assertUnprocessable();

    $response->assertJsonMissingValidationErrors([
        'media',
    ]);

    $response->assertJsonValidationErrors([
        'purchase_invoice_positions.0.amount',
        'purchase_invoice_positions.0.unit_price',
        'purchase_invoice_positions.0.total_price',
    ]);
});

test('delete purchase invoice', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/purchase-invoices/' . $this->purchaseInvoices[1]->id);
    $response->assertNoContent();

    $purchaseInvoice = $this->purchaseInvoices[1]->fresh();
    expect($purchaseInvoice->deleted_at)->not->toBeNull();
    expect($this->user->is($purchaseInvoice->getDeletedBy()))->toBeTrue();
});

test('delete purchase invoice purchase invoice not found', function (): void {
    $this->user->givePermissionTo($this->permissions['delete']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)
        ->delete('/api/purchase-invoices/' . ++$this->purchaseInvoices[2]->id);
    $response->assertNotFound();
});

test('finish purchase invoice', function (): void {
    ContactBankConnection::factory()->create([
        'contact_id' => $this->purchaseInvoices[0]->contact_id,
    ]);

    $purchaseInvoice = [
        'id' => $this->purchaseInvoices[0]->id,
    ];

    $this->user->givePermissionTo($this->permissions['finish']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/purchase-invoices/finish', $purchaseInvoice);
    $response->assertCreated();

    $responseOrder = json_decode($response->getContent())->data;

    $dbOrder = Order::query()
        ->whereKey($responseOrder->id)
        ->first();
    $dbPurchaseInvoice = $this->purchaseInvoices[0]->fresh();

    expect($dbOrder)->not->toBeEmpty();
    expect($dbOrder->client_id)->toEqual($dbPurchaseInvoice->client_id);
    expect($dbOrder->contact_id)->toEqual($dbPurchaseInvoice->contact_id);
    expect($dbOrder->currency_id)->toEqual($dbPurchaseInvoice->currency_id);
    expect($dbOrder->getFirstMedia('invoice')->id)->toEqual($dbPurchaseInvoice->media_id);
    expect($dbPurchaseInvoice->order_id)->toEqual($dbOrder->id);
    expect($dbOrder->order_type_id)->toEqual($dbPurchaseInvoice->order_type_id);
    expect($dbOrder->payment_type_id)->toEqual($dbPurchaseInvoice->payment_type_id);
    expect($dbOrder->invoice_date->toDateString())->toEqual($dbPurchaseInvoice->invoice_date->toDateString());
    expect($dbOrder->invoice_number)->toEqual($dbPurchaseInvoice->invoice_number);
    expect($dbPurchaseInvoice->total_gross_price)->toEqual($this->purchaseInvoices[0]->total_gross_price);
});

test('finish purchase invoice validation fails', function (): void {
    $this->purchaseInvoices[1]->update([
        'client_id' => null,
        'contact_id' => null,
        'order_type_id' => null,
        'invoice_number' => null,
    ]);

    $purchaseInvoice = [
        'id' => $this->purchaseInvoices[1]->id,
    ];

    $this->user->givePermissionTo($this->permissions['finish']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->post('/api/purchase-invoices/finish', $purchaseInvoice);

    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'client_id',
        'contact_id',
        'order_type_id',
        'invoice_number',
    ]);
});

test('get purchase invoice', function (): void {
    $this->purchaseInvoices[0] = $this->purchaseInvoices[0]->refresh();

    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/purchase-invoices/' . $this->purchaseInvoices[0]->id);
    $response->assertOk();

    $purchaseInvoice = json_decode($response->getContent())->data;
    expect($purchaseInvoice)->not->toBeEmpty();
    expect($purchaseInvoice->id)->toEqual($this->purchaseInvoices[0]->id);
    expect($purchaseInvoice->client_id)->toEqual($this->purchaseInvoices[0]->client_id);
    expect($purchaseInvoice->contact_id)->toEqual($this->purchaseInvoices[0]->contact_id);
    expect($purchaseInvoice->currency_id)->toEqual($this->purchaseInvoices[0]->currency_id);
    expect($purchaseInvoice->media_id)->toEqual($this->purchaseInvoices[0]->media_id);
    expect($purchaseInvoice->order_id)->toEqual($this->purchaseInvoices[0]->order_id);
    expect($purchaseInvoice->order_type_id)->toEqual($this->purchaseInvoices[0]->order_type_id);
    expect($purchaseInvoice->payment_type_id)->toEqual($this->purchaseInvoices[0]->payment_type_id);
    expect(Carbon::parse($purchaseInvoice->invoice_date)->toDateString())->toEqual($this->purchaseInvoices[0]->invoice_date->toDateString());
    expect($purchaseInvoice->invoice_number)->toEqual($this->purchaseInvoices[0]->invoice_number);
    expect($purchaseInvoice->hash)->toEqual($this->purchaseInvoices[0]->hash);
    expect($purchaseInvoice->is_net)->toEqual($this->purchaseInvoices[0]->is_net);
    expect(Carbon::parse($purchaseInvoice->created_at))->toEqual(Carbon::parse($this->purchaseInvoices[0]->created_at));
    expect(Carbon::parse($purchaseInvoice->updated_at))->toEqual(Carbon::parse($this->purchaseInvoices[0]->updated_at));
});

test('get purchase invoice purchase invoice not found', function (): void {
    $this->user->givePermissionTo($this->permissions['show']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/purchase-invoices/' . ++$this->purchaseInvoices[2]->id);
    $response->assertNotFound();
});

test('get purchase invoices', function (): void {
    $this->user->givePermissionTo($this->permissions['index']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->get('/api/purchase-invoices');
    $response->assertOk();

    $purchaseInvoices = json_decode($response->getContent())->data->data;

    expect($purchaseInvoices)->not->toBeEmpty();
    expect(count($purchaseInvoices))->toBeGreaterThanOrEqual(3);
    $this->assertObjectHasProperty('id', $purchaseInvoices[0]);
    $referencePurchaseInvoice = PurchaseInvoice::query()
        ->whereKey($purchaseInvoices[0]->id)
        ->first();

    expect($referencePurchaseInvoice)->not->toBeNull();
    expect($this->purchaseInvoices[0]->id)->toEqual($referencePurchaseInvoice->id);
    expect($this->purchaseInvoices[0]->client_id)->toEqual($referencePurchaseInvoice->client_id);
    expect($this->purchaseInvoices[0]->contact_id)->toEqual($referencePurchaseInvoice->contact_id);
    expect($this->purchaseInvoices[0]->currency_id)->toEqual($referencePurchaseInvoice->currency_id);
    expect($this->purchaseInvoices[0]->media_id)->toEqual($referencePurchaseInvoice->media_id);
    expect($this->purchaseInvoices[0]->order_id)->toEqual($referencePurchaseInvoice->order_id);
    expect($this->purchaseInvoices[0]->order_type_id)->toEqual($referencePurchaseInvoice->order_type_id);
    expect($this->purchaseInvoices[0]->payment_type_id)->toEqual($referencePurchaseInvoice->payment_type_id);
    expect(Carbon::parse($this->purchaseInvoices[0]->invoice_date)->toDateString())->toEqual($referencePurchaseInvoice->invoice_date->toDateString());
    expect($this->purchaseInvoices[0]->invoice_number)->toEqual($referencePurchaseInvoice->invoice_number);
    expect($this->purchaseInvoices[0]->hash)->toEqual($referencePurchaseInvoice->hash);
    expect($this->purchaseInvoices[0]->is_net)->toEqual($referencePurchaseInvoice->is_net);
    expect(Carbon::parse($purchaseInvoices[0]->created_at))->toEqual(Carbon::parse($referencePurchaseInvoice->created_at));
    expect(Carbon::parse($purchaseInvoices[0]->updated_at))->toEqual(Carbon::parse($referencePurchaseInvoice->updated_at));
});

test('update purchase invoice', function (): void {
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

    $responsePurchaseInvoice = json_decode($response->getContent())->data;
    $dbPurchaseInvoice = PurchaseInvoice::query()
        ->whereKey($responsePurchaseInvoice->id)
        ->first();

    expect($dbPurchaseInvoice)->not->toBeEmpty();
    expect($dbPurchaseInvoice->id)->toEqual($purchaseInvoice['id']);
    expect($dbPurchaseInvoice->uuid)->toEqual($this->purchaseInvoices[0]->uuid);
    expect($dbPurchaseInvoice->client_id)->toEqual($this->purchaseInvoices[0]->client_id);
    expect($dbPurchaseInvoice->contact_id)->toEqual($purchaseInvoice['contact_id']);
    expect($dbPurchaseInvoice->currency_id)->toEqual($purchaseInvoice['currency_id']);
    expect($dbPurchaseInvoice->media_id)->toEqual($this->purchaseInvoices[0]->media_id);
    expect($dbPurchaseInvoice->order_id)->toEqual($this->purchaseInvoices[0]->order_id);
    expect($dbPurchaseInvoice->order_type_id)->toEqual($purchaseInvoice['order_type_id']);
    expect($dbPurchaseInvoice->payment_type_id)->toEqual($purchaseInvoice['payment_type_id']);
    expect(Carbon::parse($dbPurchaseInvoice->invoice_date)->toDateString())->toEqual($purchaseInvoice['invoice_date']);
    expect($dbPurchaseInvoice->invoice_number)->toEqual($purchaseInvoice['invoice_number']);
    expect($dbPurchaseInvoice->hash)->toEqual($this->purchaseInvoices[0]->hash);
    expect($dbPurchaseInvoice->is_net)->toEqual($this->purchaseInvoices[0]->is_net);
    expect($this->user->is($dbPurchaseInvoice->getUpdatedBy()))->toBeTrue();
    expect($dbPurchaseInvoice->purchaseInvoicePositions)->toHaveCount(count($purchaseInvoice['purchase_invoice_positions']));
});

test('update purchase invoice validation fails invoice number', function (): void {
    $purchaseInvoice = [
        'id' => $this->purchaseInvoices[0]->id,
        'invoice_number' => $this->order->invoice_number,
        'contact_id' => $this->order->contact_id,
        'client_id' => $this->order->client_id,
    ];

    $this->user->givePermissionTo($this->permissions['update']);
    Sanctum::actingAs($this->user, ['user']);

    $response = $this->actingAs($this->user)->put('/api/purchase-invoices', $purchaseInvoice);
    $response->assertUnprocessable();

    $response->assertJsonValidationErrors([
        'invoice_number',
    ]);
});
