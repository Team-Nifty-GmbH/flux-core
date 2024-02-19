<?php

namespace FluxErp\Tests\Feature\Api;

use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\VatRate;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class PurchaseInvoiceTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $clients;

    private Collection $contacts;

    private Collection $currencies;

    private Collection $orders;

    private Collection $orderTypes;

    private Collection $paymentTypes;

    public Collection $purchaseInvoices;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->clients = Client::factory()->count(2)->create();

        $this->paymentTypes = PaymentType::factory()->count(2)->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->contacts = Contact::factory()->count(2)
            ->has(Address::factory()->set('client_id', $this->dbClient->id))
            ->for(PriceList::factory())
            ->create([
                'client_id' => $this->dbClient->id,
                'payment_type_id' => $this->paymentTypes->random()->id,
                'discount_target' => 0,
            ]);

        $this->currencies = Currency::factory()->count(3)->create();
        Currency::query()->first()->update(['is_default' => true]);

        $languages = Language::factory()->count(3)->create();

        $this->orderTypes = OrderType::factory()->count(3)->create([
            'client_id' => $this->dbClient->id,
            'order_type_enum' => OrderTypeEnum::Purchase,
        ]);

        $vatRates = VatRate::factory()->count(3)->create();
        $this->purchaseInvoices = PurchaseInvoice::factory()
            ->has(PurchaseInvoicePosition::factory()->count(2)->set('vat_rate_id', $vatRates->random()->id))
            ->count(3)
            ->afterCreating(function (PurchaseInvoice $purchaseInvoice) {
                $purchaseInvoice->addMedia(UploadedFile::fake()->image($purchaseInvoice->invoice_number . '.jpeg'))
                    ->toMediaCollection('purchase_invoice');
            })
            ->create([
                'client_id' => $this->dbClient->id,
                'order_type_id' => $this->orderTypes->random()->id,
                'payment_type_id' => $this->paymentTypes->random()->id,
                'currency_id' => $this->currencies->random()->id,
                'contact_id' => $this->contacts->random()->id,
            ]);

        $this->orders = Order::factory()->count(3)->create([
            'client_id' => $this->clients[0]->id,
            'currency_id' => $this->currencies->random()->id,
            'order_type_id' => $this->orderTypes[0]->id,
            'payment_type_id' => $this->paymentTypes[0]->id,
            'contact_id' => $this->contacts->random()->id,
            'address_invoice_id' => $this->contacts->random()->addresses->first()->id,
            'language_id' => $languages->random()->id,
            'is_locked' => true,
        ]);

        $this->orders[0]->invoice_number = Str::uuid()->toString();
        $this->orders[0]->save();

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

    public function test_get_purchase_invoice()
    {
        $this->purchaseInvoices[0] = $this->purchaseInvoices[0]->refresh();

        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/purchase-invoices/' . $this->purchaseInvoices[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $purchaseInvoice = $json->data;
        $this->assertNotEmpty($purchaseInvoice);
        $this->assertEquals($this->purchaseInvoices[0]->id, $purchaseInvoice->id);
        $this->assertEquals($this->purchaseInvoices[0]->client_id, $purchaseInvoice->client_id);
        $this->assertEquals($this->purchaseInvoices[0]->contact_id, $purchaseInvoice->contact_id);
        $this->assertEquals($this->purchaseInvoices[0]->currency_id, $purchaseInvoice->currency_id);
        $this->assertEquals($this->purchaseInvoices[0]->media_id, $purchaseInvoice->media_id);
        $this->assertEquals($this->purchaseInvoices[0]->order_id, $purchaseInvoice->order_id);
        $this->assertEquals($this->purchaseInvoices[0]->order_type_id, $purchaseInvoice->order_type_id);
        $this->assertEquals($this->purchaseInvoices[0]->payment_type_id, $purchaseInvoice->payment_type_id);
        $this->assertEquals($this->purchaseInvoices[0]->invoice_date,
            ! is_null($purchaseInvoice->invoice_date) ? Carbon::parse($purchaseInvoice->invoice_date) : null);
        $this->assertEquals($this->purchaseInvoices[0]->invoice_number, $purchaseInvoice->invoice_number);
        $this->assertEquals($this->purchaseInvoices[0]->hash, $purchaseInvoice->hash);
        $this->assertEquals($this->purchaseInvoices[0]->is_net, $purchaseInvoice->is_net);
        $this->assertEquals(Carbon::parse($this->purchaseInvoices[0]->created_at),
            Carbon::parse($purchaseInvoice->created_at));
        $this->assertEquals(Carbon::parse($this->purchaseInvoices[0]->updated_at),
            Carbon::parse($purchaseInvoice->updated_at));
    }

    public function test_get_purchase_invoice_purchase_invoice_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/purchase-invoices/' . ++$this->purchaseInvoices[2]->id);
        $response->assertStatus(404);
    }

    public function test_get_purchase_invoices()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/purchase-invoices');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $purchaseInvoices = $json->data->data;
        $referencePurchaseInvoice = PurchaseInvoice::query()->first();

        $this->assertNotEmpty($purchaseInvoices);
        $this->assertEquals($referencePurchaseInvoice->id, $this->purchaseInvoices[0]->id);
        $this->assertEquals($referencePurchaseInvoice->client_id, $this->purchaseInvoices[0]->client_id);
        $this->assertEquals($referencePurchaseInvoice->contact_id, $this->purchaseInvoices[0]->contact_id);
        $this->assertEquals($referencePurchaseInvoice->currency_id, $this->purchaseInvoices[0]->currency_id);
        $this->assertEquals($referencePurchaseInvoice->media_id, $this->purchaseInvoices[0]->media_id);
        $this->assertEquals($referencePurchaseInvoice->order_id, $this->purchaseInvoices[0]->order_id);
        $this->assertEquals($referencePurchaseInvoice->order_type_id, $this->purchaseInvoices[0]->order_type_id);
        $this->assertEquals($referencePurchaseInvoice->payment_type_id, $this->purchaseInvoices[0]->payment_type_id);
        $this->assertEquals(Carbon::parse($referencePurchaseInvoice->invoice_date),
            ! is_null($this->purchaseInvoices[0]->invoice_date) ? Carbon::parse($this->purchaseInvoices[0]->invoice_date) : null);
        $this->assertEquals($referencePurchaseInvoice->invoice_number, $this->purchaseInvoices[0]->invoice_number);
        $this->assertEquals($referencePurchaseInvoice->hash, $this->purchaseInvoices[0]->hash);
        $this->assertEquals($referencePurchaseInvoice->is_net, $this->purchaseInvoices[0]->is_net);
        $this->assertEquals(Carbon::parse($referencePurchaseInvoice->created_at),
            Carbon::parse($purchaseInvoices[0]->created_at));
        $this->assertEquals(Carbon::parse($referencePurchaseInvoice->updated_at),
            Carbon::parse($purchaseInvoices[0]->updated_at));
    }

    public function test_create_purchase_invoice_minimum()
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

        $this->assertEquals($this->user->id, $dbPurchaseInvoice->created_by->id);
        $this->assertEquals($this->user->id, $dbPurchaseInvoice->updated_by->id);
    }

    public function test_create_purchase_invoice_validation_fails()
    {
        $purchaseInvoice = [
            'client_id' => $this->dbClient->id,
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

    public function test_create_purchase_invoice_validation_fails_positions()
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

    public function test_update_purchase_invoice()
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
        $this->assertEquals($purchaseInvoice['contact_id'], $dbPurchaseInvoice->contact_id);
        $this->assertEquals($purchaseInvoice['currency_id'], $dbPurchaseInvoice->currency_id);
        $this->assertEquals($purchaseInvoice['order_type_id'], $dbPurchaseInvoice->order_type_id);
        $this->assertEquals($purchaseInvoice['payment_type_id'], $dbPurchaseInvoice->payment_type_id);
        $this->assertEquals($purchaseInvoice['invoice_date'], Carbon::parse($dbPurchaseInvoice->invoice_date)->toDateString());
        $this->assertEquals($purchaseInvoice['invoice_number'], $dbPurchaseInvoice->invoice_number);
        $this->assertEquals($this->user->id, $dbPurchaseInvoice->updated_by->id);
        $this->assertCount(
            count($purchaseInvoice['purchase_invoice_positions']),
            $dbPurchaseInvoice->purchaseInvoicePositions
        );
    }

    public function test_update_purchase_invoice_validation_fails_invoice_number()
    {
        $purchaseInvoice = [
            'id' => $this->purchaseInvoices[0]->id,
            'invoice_number' => $this->orders[0]->invoice_number,
            'contact_id' => $this->orders[0]->contact_id,
            'client_id' => $this->orders[0]->client_id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/purchase-invoices', $purchaseInvoice);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'invoice_number',
        ]);
    }

    public function test_delete_purchase_invoice()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/purchase-invoices/' . $this->purchaseInvoices[1]->id);
        $response->assertStatus(204);

        $purchaseInvoice = $this->purchaseInvoices[1]->fresh();
        $this->assertNotNull($purchaseInvoice->deleted_at);
        $this->assertEquals($this->user->id, $purchaseInvoice->deleted_by->id);
    }

    public function test_delete_purchase_invoice_purchase_invoice_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/purchase-invoices/' . ++$this->purchaseInvoices[2]->id);
        $response->assertStatus(404);
    }

    public function test_finish_purchase_invoice()
    {
        $purchaseInvoice = [
            'id' => $this->purchaseInvoices[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/purchase-invoices/finish', $purchaseInvoice);
        $response->assertStatus(200);

        $responseOrder = json_decode($response->getContent())->data;

        $dbOrder = Order::query()
            ->whereKey($responseOrder->id)
            ->first();
        $dbPurchaseInvoice = $this->purchaseInvoices[0]->fresh();

        $this->assertNotEmpty($dbOrder);
        $this->assertSoftDeleted($dbPurchaseInvoice);
        $this->assertEquals($dbPurchaseInvoice->client_id, $dbOrder->client_id);
        $this->assertEquals($dbPurchaseInvoice->contact_id, $dbOrder->contact_id);
        $this->assertEquals($dbPurchaseInvoice->currency_id, $dbOrder->currency_id);
        $this->assertEquals($dbPurchaseInvoice->media_id, $dbOrder->getFirstMedia('invoice')->id);
        $this->assertEquals($dbPurchaseInvoice->order_id, $dbOrder->id);
        $this->assertEquals($dbPurchaseInvoice->order_type_id, $dbOrder->order_type_id);
        $this->assertEquals($dbPurchaseInvoice->payment_type_id, $dbOrder->payment_type_id);
        $this->assertEquals(
            is_null($dbPurchaseInvoice->invoice_date)
                ? now()->toDateString()
                : $dbPurchaseInvoice->invoice_date->toDateString(),
            $dbOrder->invoice_date->toDateString()
        );
        $this->assertEquals($dbPurchaseInvoice->invoice_number, $dbOrder->invoice_number);
    }

    public function test_finish_purchase_invoice_validation_fails()
    {
        $this->purchaseInvoices[1]->client_id = null;
        $this->purchaseInvoices[1]->contact_id = null;
        $this->purchaseInvoices[1]->order_type_id = null;
        $this->purchaseInvoices[1]->invoice_number = null;
        $this->purchaseInvoices[1]->save();

        $purchaseInvoice = [
            'id' => $this->purchaseInvoices[1]->id,
        ];

        $this->user->givePermissionTo($this->permissions['finish']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/purchase-invoices/finish', $purchaseInvoice);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'invoice_number',
            'client_id',
            'contact_id',
            'order_type_id',
        ]);
    }
}
