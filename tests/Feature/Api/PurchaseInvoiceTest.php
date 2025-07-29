<?php

namespace FluxErp\Tests\Feature\Api;

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
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class PurchaseInvoiceTest extends BaseSetup
{
    public Collection $purchaseInvoices;

    private Collection $clients;

    private Collection $contacts;

    private Collection $currencies;

    private Collection $orders;

    private Collection $orderTypes;

    private Collection $paymentTypes;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();
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
    }

    public function test_create_purchase_invoice_maximum(): void
    {
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
        $response->assertStatus(201);

        $responsePurchaseInvoice = json_decode($response->getContent())->data;
        $dbPurchaseInvoice = PurchaseInvoice::query()
            ->whereKey($responsePurchaseInvoice->id)
            ->first();

        $this->assertNotEmpty($dbPurchaseInvoice);
        $this->assertEquals($purchaseInvoice['uuid'], $dbPurchaseInvoice->uuid);
        $this->assertEquals($purchaseInvoice['client_id'], $dbPurchaseInvoice->client_id);
        $this->assertEquals($purchaseInvoice['contact_id'], $dbPurchaseInvoice->contact_id);
        $this->assertEquals($purchaseInvoice['currency_id'], $dbPurchaseInvoice->currency_id);
        $this->assertNotNull($dbPurchaseInvoice->media_id);
        $this->assertNull($dbPurchaseInvoice->order_id);
        $this->assertEquals($purchaseInvoice['order_type_id'], $dbPurchaseInvoice->order_type_id);
        $this->assertEquals($purchaseInvoice['payment_type_id'], $dbPurchaseInvoice->payment_type_id);
        $this->assertEquals(
            $purchaseInvoice['invoice_date'],
            Carbon::parse($dbPurchaseInvoice->invoice_date)->toDateString()
        );
        $this->assertEquals($purchaseInvoice['invoice_number'], $dbPurchaseInvoice->invoice_number);
        $this->assertEquals($purchaseInvoice['is_net'], $dbPurchaseInvoice->is_net);
        $this->assertTrue($this->user->is($dbPurchaseInvoice->getCreatedBy()));
        $this->assertTrue($this->user->is($dbPurchaseInvoice->getUpdatedBy()));

        $dbPurchaseInvoicePositions = $dbPurchaseInvoice->purchaseInvoicePositions;
        $this->assertCount(count($purchaseInvoice['purchase_invoice_positions']), $dbPurchaseInvoicePositions);
        $this->assertEquals(
            $purchaseInvoice['purchase_invoice_positions'][0]['ledger_account_id'],
            $dbPurchaseInvoicePositions[0]->ledger_account_id
        );
        $this->assertEquals(
            $purchaseInvoice['purchase_invoice_positions'][0]['product_id'],
            $dbPurchaseInvoicePositions[0]->product_id
        );
        $this->assertEquals(
            $purchaseInvoice['purchase_invoice_positions'][0]['vat_rate_id'],
            $dbPurchaseInvoicePositions[0]->vat_rate_id
        );
        $this->assertEquals(
            $purchaseInvoice['purchase_invoice_positions'][0]['name'],
            $dbPurchaseInvoicePositions[0]->name
        );
        $this->assertEquals(
            $purchaseInvoice['purchase_invoice_positions'][0]['amount'],
            $dbPurchaseInvoicePositions[0]->amount
        );
        $this->assertEquals(
            bcround($purchaseInvoice['purchase_invoice_positions'][0]['unit_price'], 2),
            bcround($dbPurchaseInvoicePositions[0]->unit_price, 2)
        );
        $this->assertEquals(
            bcround($purchaseInvoice['purchase_invoice_positions'][0]['total_price'], 2),
            bcround($dbPurchaseInvoicePositions[0]->total_price, 2)
        );
    }

    public function test_create_purchase_invoice_minimum(): void
    {
        $purchaseInvoice = [
            'media' => UploadedFile::fake()->image('test_purchase_invoice.jpeg'),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/purchase-invoices', $purchaseInvoice);
        $response->assertStatus(201);

        $responsePurchaseInvoice = json_decode($response->getContent())->data;
        $dbPurchaseInvoice = PurchaseInvoice::query()
            ->whereKey($responsePurchaseInvoice->id)
            ->first();

        $this->assertNotEmpty($dbPurchaseInvoice);
        $this->assertEquals(Client::default()?->id, $dbPurchaseInvoice->client_id);
        $this->assertNull($dbPurchaseInvoice->contact_id);
        $this->assertNull($dbPurchaseInvoice->currency_id);
        $this->assertNotNull($dbPurchaseInvoice->media_id);
        $this->assertNull($dbPurchaseInvoice->order_id);
        $this->assertNull($dbPurchaseInvoice->order_type_id);
        $this->assertNull($dbPurchaseInvoice->payment_type_id);
        $this->assertEquals(
            Carbon::now()->toDateString(),
            Carbon::parse($dbPurchaseInvoice->invoice_date)->toDateString()
        );
        $this->assertNull($dbPurchaseInvoice->invoice_number);
        $this->assertFalse($dbPurchaseInvoice->is_net);
        $this->assertTrue($this->user->is($dbPurchaseInvoice->getCreatedBy()));
        $this->assertTrue($this->user->is($dbPurchaseInvoice->getUpdatedBy()));
        $this->assertEmpty($dbPurchaseInvoice->purchaseInvoicePositions);
    }

    public function test_create_purchase_invoice_validation_fails(): void
    {
        $purchaseInvoice = [
            'client_id' => $this->dbClient->getKey(),
            'purchase_invoice_positions' => [],
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/purchase-invoices', $purchaseInvoice);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'media',
        ]);

        $response->assertJsonMissingValidationErrors([
            'purchase_invoice_positions.0.amount',
            'purchase_invoice_positions.0.unit_price',
            'purchase_invoice_positions.0.total_price',
        ]);
    }

    public function test_create_purchase_invoice_validation_fails_positions(): void
    {
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
        $response->assertStatus(422);

        $response->assertJsonMissingValidationErrors([
            'media',
        ]);

        $response->assertJsonValidationErrors([
            'purchase_invoice_positions.0.amount',
            'purchase_invoice_positions.0.unit_price',
            'purchase_invoice_positions.0.total_price',
        ]);
    }

    public function test_delete_purchase_invoice(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/purchase-invoices/' . $this->purchaseInvoices[1]->id);
        $response->assertStatus(204);

        $purchaseInvoice = $this->purchaseInvoices[1]->fresh();
        $this->assertNotNull($purchaseInvoice->deleted_at);
        $this->assertTrue($this->user->is($purchaseInvoice->getDeletedBy()));
    }

    public function test_delete_purchase_invoice_purchase_invoice_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)
            ->delete('/api/purchase-invoices/' . ++$this->purchaseInvoices[2]->id);
        $response->assertStatus(404);
    }

    public function test_finish_purchase_invoice(): void
    {
        ContactBankConnection::factory()->create([
            'contact_id' => $this->purchaseInvoices[0]->contact_id,
        ]);

        $purchaseInvoice = [
            'id' => $this->purchaseInvoices[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/purchase-invoices/finish', $purchaseInvoice);
        $response->assertStatus(201);

        $responseOrder = json_decode($response->getContent())->data;

        $dbOrder = Order::query()
            ->whereKey($responseOrder->id)
            ->first();
        $dbPurchaseInvoice = $this->purchaseInvoices[0]->fresh();

        $this->assertNotEmpty($dbOrder);
        $this->assertEquals($dbPurchaseInvoice->client_id, $dbOrder->client_id);
        $this->assertEquals($dbPurchaseInvoice->contact_id, $dbOrder->contact_id);
        $this->assertEquals($dbPurchaseInvoice->currency_id, $dbOrder->currency_id);
        $this->assertEquals($dbPurchaseInvoice->media_id, $dbOrder->getFirstMedia('invoice')->id);
        $this->assertEquals($dbOrder->id, $dbPurchaseInvoice->order_id);
        $this->assertEquals($dbPurchaseInvoice->order_type_id, $dbOrder->order_type_id);
        $this->assertEquals($dbPurchaseInvoice->payment_type_id, $dbOrder->payment_type_id);
        $this->assertEquals($dbPurchaseInvoice->invoice_date->toDateString(), $dbOrder->invoice_date->toDateString());
        $this->assertEquals($dbPurchaseInvoice->invoice_number, $dbOrder->invoice_number);
        $this->assertEquals($this->purchaseInvoices[0]->total_gross_price, $dbPurchaseInvoice->total_gross_price);
    }

    public function test_finish_purchase_invoice_validation_fails(): void
    {
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

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'client_id',
            'contact_id',
            'order_type_id',
            'invoice_number',
        ]);
    }

    public function test_get_purchase_invoice(): void
    {
        $this->purchaseInvoices[0] = $this->purchaseInvoices[0]->refresh();

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/purchase-invoices/' . $this->purchaseInvoices[0]->id);
        $response->assertStatus(200);

        $purchaseInvoice = json_decode($response->getContent())->data;
        $this->assertNotEmpty($purchaseInvoice);
        $this->assertEquals($this->purchaseInvoices[0]->id, $purchaseInvoice->id);
        $this->assertEquals($this->purchaseInvoices[0]->client_id, $purchaseInvoice->client_id);
        $this->assertEquals($this->purchaseInvoices[0]->contact_id, $purchaseInvoice->contact_id);
        $this->assertEquals($this->purchaseInvoices[0]->currency_id, $purchaseInvoice->currency_id);
        $this->assertEquals($this->purchaseInvoices[0]->media_id, $purchaseInvoice->media_id);
        $this->assertEquals($this->purchaseInvoices[0]->order_id, $purchaseInvoice->order_id);
        $this->assertEquals($this->purchaseInvoices[0]->order_type_id, $purchaseInvoice->order_type_id);
        $this->assertEquals($this->purchaseInvoices[0]->payment_type_id, $purchaseInvoice->payment_type_id);
        $this->assertEquals(
            $this->purchaseInvoices[0]->invoice_date->toDateString(),
            Carbon::parse($purchaseInvoice->invoice_date)->toDateString()
        );
        $this->assertEquals($this->purchaseInvoices[0]->invoice_number, $purchaseInvoice->invoice_number);
        $this->assertEquals($this->purchaseInvoices[0]->hash, $purchaseInvoice->hash);
        $this->assertEquals($this->purchaseInvoices[0]->is_net, $purchaseInvoice->is_net);
        $this->assertEquals(Carbon::parse($this->purchaseInvoices[0]->created_at),
            Carbon::parse($purchaseInvoice->created_at));
        $this->assertEquals(Carbon::parse($this->purchaseInvoices[0]->updated_at),
            Carbon::parse($purchaseInvoice->updated_at));
    }

    public function test_get_purchase_invoice_purchase_invoice_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/purchase-invoices/' . ++$this->purchaseInvoices[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_purchase_invoices(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/purchase-invoices');
        $response->assertStatus(200);

        $purchaseInvoices = json_decode($response->getContent())->data->data;

        $this->assertNotEmpty($purchaseInvoices);
        $this->assertGreaterThanOrEqual(3, count($purchaseInvoices));
        $this->assertObjectHasProperty('id', $purchaseInvoices[0]);
        $referencePurchaseInvoice = PurchaseInvoice::query()
            ->whereKey($purchaseInvoices[0]->id)
            ->first();

        $this->assertNotNull($referencePurchaseInvoice);
        $this->assertEquals($referencePurchaseInvoice->id, $this->purchaseInvoices[0]->id);
        $this->assertEquals($referencePurchaseInvoice->client_id, $this->purchaseInvoices[0]->client_id);
        $this->assertEquals($referencePurchaseInvoice->contact_id, $this->purchaseInvoices[0]->contact_id);
        $this->assertEquals($referencePurchaseInvoice->currency_id, $this->purchaseInvoices[0]->currency_id);
        $this->assertEquals($referencePurchaseInvoice->media_id, $this->purchaseInvoices[0]->media_id);
        $this->assertEquals($referencePurchaseInvoice->order_id, $this->purchaseInvoices[0]->order_id);
        $this->assertEquals($referencePurchaseInvoice->order_type_id, $this->purchaseInvoices[0]->order_type_id);
        $this->assertEquals($referencePurchaseInvoice->payment_type_id, $this->purchaseInvoices[0]->payment_type_id);
        $this->assertEquals(
            $referencePurchaseInvoice->invoice_date->toDateString(),
            Carbon::parse($this->purchaseInvoices[0]->invoice_date)->toDateString()
        );
        $this->assertEquals($referencePurchaseInvoice->invoice_number, $this->purchaseInvoices[0]->invoice_number);
        $this->assertEquals($referencePurchaseInvoice->hash, $this->purchaseInvoices[0]->hash);
        $this->assertEquals($referencePurchaseInvoice->is_net, $this->purchaseInvoices[0]->is_net);
        $this->assertEquals(Carbon::parse($referencePurchaseInvoice->created_at),
            Carbon::parse($purchaseInvoices[0]->created_at));
        $this->assertEquals(Carbon::parse($referencePurchaseInvoice->updated_at),
            Carbon::parse($purchaseInvoices[0]->updated_at));
    }

    public function test_update_purchase_invoice(): void
    {
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
        $response->assertStatus(200);

        $responsePurchaseInvoice = json_decode($response->getContent())->data;
        $dbPurchaseInvoice = PurchaseInvoice::query()
            ->whereKey($responsePurchaseInvoice->id)
            ->first();

        $this->assertNotEmpty($dbPurchaseInvoice);
        $this->assertEquals($purchaseInvoice['id'], $dbPurchaseInvoice->id);
        $this->assertEquals($this->purchaseInvoices[0]->uuid, $dbPurchaseInvoice->uuid);
        $this->assertEquals($this->purchaseInvoices[0]->client_id, $dbPurchaseInvoice->client_id);
        $this->assertEquals($purchaseInvoice['contact_id'], $dbPurchaseInvoice->contact_id);
        $this->assertEquals($purchaseInvoice['currency_id'], $dbPurchaseInvoice->currency_id);
        $this->assertEquals($this->purchaseInvoices[0]->media_id, $dbPurchaseInvoice->media_id);
        $this->assertEquals($this->purchaseInvoices[0]->order_id, $dbPurchaseInvoice->order_id);
        $this->assertEquals($purchaseInvoice['order_type_id'], $dbPurchaseInvoice->order_type_id);
        $this->assertEquals($purchaseInvoice['payment_type_id'], $dbPurchaseInvoice->payment_type_id);
        $this->assertEquals(
            $purchaseInvoice['invoice_date'],
            Carbon::parse($dbPurchaseInvoice->invoice_date)->toDateString()
        );
        $this->assertEquals($purchaseInvoice['invoice_number'], $dbPurchaseInvoice->invoice_number);
        $this->assertEquals($this->purchaseInvoices[0]->hash, $dbPurchaseInvoice->hash);
        $this->assertEquals($this->purchaseInvoices[0]->is_net, $dbPurchaseInvoice->is_net);
        $this->assertTrue($this->user->is($dbPurchaseInvoice->getUpdatedBy()));
        $this->assertCount(
            count($purchaseInvoice['purchase_invoice_positions']),
            $dbPurchaseInvoice->purchaseInvoicePositions
        );
    }

    public function test_update_purchase_invoice_validation_fails_invoice_number(): void
    {
        $purchaseInvoice = [
            'id' => $this->purchaseInvoices[0]->id,
            'invoice_number' => $this->order->invoice_number,
            'contact_id' => $this->order->contact_id,
            'client_id' => $this->order->client_id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/purchase-invoices', $purchaseInvoice);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'invoice_number',
        ]);
    }
}
