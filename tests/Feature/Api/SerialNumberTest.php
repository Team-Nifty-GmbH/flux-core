<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

class SerialNumberTest extends BaseSetup
{
    use DatabaseTransactions;

    private Collection $products;

    private Collection $serialNumbers;

    private Collection $serialNumberRanges;

    private Collection $addresses;

    private Collection $orderPositions;

    private array $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $language = Language::factory()->create();

        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
            'order_type_enum' => OrderTypeEnum::Purchase,
        ]);

        $paymentType = PaymentType::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->products = Product::factory()->count(3)->create([
            'client_id' => $this->dbClient->id,
        ]);

        $this->serialNumberRanges = new Collection();
        foreach ($this->products as $product) {
            $this->serialNumberRanges->push(SerialNumberRange::factory()->create([
                'model_type' => Product::class,
                'model_id' => $product->id,
                'type' => 'product',
                'client_id' => $product->client_id,
            ]));
        }

        $this->serialNumbers = SerialNumber::factory()->count(3)->create();

        $this->addresses = Address::factory()->count(3)->create([
            'contact_id' => $contact->id,
            'is_main_address' => false,
            'client_id' => $this->dbClient->id,
        ]);

        $order = Order::factory()->create([
            'address_invoice_id' => $this->addresses[0]->id,
            'address_delivery_id' => $this->addresses[0]->id,
            'client_id' => $this->dbClient->id,
            'currency_id' => Currency::factory()->create()->id,
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => PriceList::factory()->create()->id,
        ]);

        $this->orderPositions = OrderPosition::factory()->count(3)->create([
            'order_id' => $order->id,
            'client_id' => $this->dbClient->id,
            'sort_number' => 0,
        ]);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.serial-numbers.{id}.get'),
            'index' => Permission::findOrCreate('api.serial-numbers.get'),
            'create' => Permission::findOrCreate('api.serial-numbers.post'),
            'update' => Permission::findOrCreate('api.serial-numbers.put'),
            'delete' => Permission::findOrCreate('api.serial-numbers.{id}.delete'),
        ];
    }

    public function test_get_serial_number()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/serial-numbers/' . $this->serialNumbers[0]->id);
        $response->assertStatus(200);

        $serialNumber = json_decode($response->getContent())->data;

        $this->assertEquals($this->serialNumbers[0]->id, $serialNumber->id);
        $this->assertEquals($this->serialNumbers[0]->serial_number_range_id, $serialNumber->serial_number_range_id);
        $this->assertEquals($this->serialNumbers[0]->serial_number, $serialNumber->serial_number);
        $this->assertEquals($this->serialNumbers[0]->order_position_id, $serialNumber->order_position_id);
        $this->assertEquals($this->serialNumbers[0]->address_id, $serialNumber->address_id);
        $this->assertEquals($this->serialNumbers[0]->product_id, $serialNumber->product_id);
    }

    public function test_get_serial_number_serial_number_not_found()
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/serial-numbers/' . Str::uuid());
        $response->assertStatus(404);
    }

    public function test_get_serial_numbers()
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->get('/api/serial-numbers');
        $response->assertStatus(200);

        $serialNumbers = json_decode($response->getContent())->data->data;

        $this->assertEquals($this->serialNumbers[0]->id, $serialNumbers[0]->id);
        $this->assertEquals($this->serialNumbers[0]->serial_number_range_id, $serialNumbers[0]->serial_number_range_id);
        $this->assertEquals($this->serialNumbers[0]->serial_number, $serialNumbers[0]->serial_number);
        $this->assertEquals($this->serialNumbers[0]->order_position_id, $serialNumbers[0]->order_position_id);
        $this->assertEquals($this->serialNumbers[0]->address_id, $serialNumbers[0]->address_id);
        $this->assertEquals($this->serialNumbers[0]->product_id, $serialNumbers[0]->product_id);
    }

    public function test_create_serial_number()
    {
        $serialNumber = [
            'serial_number_range_id' => $this->serialNumberRanges[0]->id,
            'product_id' => $this->products[0]->id,
            'address_id' => $this->addresses[0]->id,
            'order_position_id' => $this->orderPositions[0]->id,
            'serial_number' => Str::random(),
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/serial-numbers', $serialNumber);
        $response->assertStatus(201);

        $responseSerialNumber = json_decode($response->getContent())->data;

        $dbSerialNumber = SerialNumber::query()
            ->whereKey($responseSerialNumber->id)
            ->first();

        $this->assertEquals($serialNumber['serial_number_range_id'], $dbSerialNumber->serial_number_range_id);
        $this->assertEquals($serialNumber['product_id'], $dbSerialNumber->product_id);
        $this->assertEquals($serialNumber['address_id'], $dbSerialNumber->address_id);
        $this->assertEquals($serialNumber['order_position_id'], $dbSerialNumber->order_position_id);
        $this->assertEquals($serialNumber['serial_number'], $dbSerialNumber->serial_number);
    }

    public function test_create_serial_number_validation_fails()
    {
        $serialNumber = [
            'serial_number_range_id' => $this->serialNumberRanges[0]->id,
            'product_id' => $this->products[0]->id,
            'address_id' => $this->addresses[0]->id,
            'order_position_id' => $this->orderPositions[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->post('/api/serial-numbers', $serialNumber);
        $response->assertStatus(422);
    }

    public function test_update_serial_number()
    {
        $serialNumber = [
            'id' => $this->serialNumbers[0]->id,
            'product_id' => $this->products[0]->id,
            'address_id' => $this->addresses[0]->id,
            'order_position_id' => $this->orderPositions[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/serial-numbers', $serialNumber);
        $response->assertStatus(200);

        $responseSerialNumber = json_decode($response->getContent())->data;

        $dbSerialNumber = SerialNumber::query()
            ->whereKey($responseSerialNumber->id)
            ->first();

        $this->assertEquals($serialNumber['id'], $dbSerialNumber->id);
        $this->assertEquals($serialNumber['product_id'], $dbSerialNumber->product_id);
        $this->assertEquals($serialNumber['address_id'], $dbSerialNumber->address_id);
        $this->assertEquals($serialNumber['order_position_id'], $dbSerialNumber->order_position_id);
        $this->assertEquals($this->serialNumbers[0]->serial_number, $dbSerialNumber->serial_number);
        $this->assertEquals($this->serialNumbers[0]->serial_number_range_id, $dbSerialNumber->serial_number_range_id);
    }

    public function test_update_serial_number_with_additional_columns()
    {
        $additionalColumn = AdditionalColumn::factory()->create([
            'model_type' => SerialNumber::class,
        ]);

        $serialNumber = [
            'id' => $this->serialNumbers[0]->id,
            'product_id' => $this->products[0]->id,
            'address_id' => $this->addresses[0]->id,
            'order_position_id' => $this->orderPositions[0]->id,
            $additionalColumn->name => 'New Value',
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/serial-numbers', $serialNumber);
        $response->assertStatus(200);

        $responseSerialNumber = json_decode($response->getContent())->data;

        $dbSerialNumber = SerialNumber::query()
            ->whereKey($responseSerialNumber->id)
            ->first();

        $this->assertEquals($serialNumber['id'], $dbSerialNumber->id);
        $this->assertEquals($serialNumber['product_id'], $dbSerialNumber->product_id);
        $this->assertEquals($serialNumber['address_id'], $dbSerialNumber->address_id);
        $this->assertEquals($serialNumber['order_position_id'], $dbSerialNumber->order_position_id);
        $this->assertEquals($serialNumber[$additionalColumn->name], $dbSerialNumber->{$additionalColumn->name});
        $this->assertEquals($this->serialNumbers[0]->serial_number, $dbSerialNumber->serial_number);
        $this->assertEquals($this->serialNumbers[0]->serial_number_range_id, $dbSerialNumber->serial_number_range_id);
    }

    public function test_update_serial_number_validation_fails()
    {
        $serialNumber = [
            'product_id' => $this->products[0]->id,
            'address_id' => $this->addresses[0]->id,
            'order_position_id' => $this->orderPositions[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/serial-numbers', $serialNumber);
        $response->assertStatus(422);
    }

    public function test_update_serial_number_serial_number_has_product_id()
    {
        $this->serialNumbers[1]->product_id = $this->products[0]->id;
        $this->serialNumbers[1]->save();

        $serialNumber = [
            'id' => $this->serialNumbers[1]->id,
            'product_id' => $this->products[1]->id,
            'address_id' => $this->addresses[0]->id,
            'order_position_id' => $this->orderPositions[0]->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/serial-numbers', $serialNumber);
        $response->assertStatus(422);
    }

    public function test_update_serial_number_serial_number_has_order_position_id()
    {
        $this->serialNumbers[1]->order_position_id = $this->orderPositions[0]->id;
        $this->serialNumbers[1]->save();

        $serialNumber = [
            'id' => $this->serialNumbers[1]->id,
            'product_id' => $this->products[0]->id,
            'address_id' => $this->addresses[0]->id,
            'order_position_id' => $this->orderPositions[1]->id,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->put('/api/serial-numbers', $serialNumber);
        $response->assertStatus(422);
    }

    public function test_delete_serial_number()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/serial-numbers/' . $this->serialNumbers[0]->id);
        $response->assertStatus(204);

        $this->assertFalse(SerialNumber::query()->whereKey($this->serialNumbers[0]->id)->exists());
    }

    public function test_delete_serial_number_serial_number_not_found()
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->actingAs($this->user)->delete('/api/serial-numbers/' . ++$this->serialNumbers[2]->id);
        $response->assertStatus(404);
    }
}
