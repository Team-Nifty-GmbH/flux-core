<?php

namespace FluxErp\Tests\Feature\Api;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
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
use FluxErp\Models\User;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;

class CommissionTest extends BaseSetup
{
    private User $agent;

    private Collection $commissions;

    private Order $order;

    private OrderPosition $orderPosition;

    private array $permissions;

    protected function setUp(): void
    {
        parent::setUp();

        $dbClient = Client::factory()->create();

        $language = Language::factory()->create();
        $this->agent = User::factory()->create([
            'language_id' => $language->id,
        ]);
        $this->agent->clients()->attach($dbClient->id);

        $contact = Contact::factory()->create([
            'client_id' => $dbClient->id,
        ]);

        $currency = Currency::factory()->create();
        $priceList = PriceList::factory()->create();
        $paymentType = PaymentType::factory()->create();
        $language = Language::factory()->create();
        $orderType = OrderType::factory()->create([
            'client_id' => $dbClient->id,
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $addressInvoice = Address::factory()->create([
            'client_id' => $dbClient->id,
            'contact_id' => $contact->id,
        ]);

        $addressDelivery = Address::factory()->create([
            'client_id' => $dbClient->id,
            'contact_id' => $contact->id,
        ]);

        $this->order = Order::factory()->create([
            'client_id' => $dbClient->id,
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
            'client_id' => $dbClient->id,
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

        $this->user->clients()->attach($dbClient->id);

        $this->permissions = [
            'show' => Permission::findOrCreate('api.commissions.{id}.get'),
            'index' => Permission::findOrCreate('api.commissions.get'),
            'create' => Permission::findOrCreate('api.commissions.post'),
            'update' => Permission::findOrCreate('api.commissions.put'),
            'delete' => Permission::findOrCreate('api.commissions.{id}.delete'),
        ];
    }

    public function test_commission_avatar_url_method(): void
    {
        $commission = $this->commissions[0];

        $avatarUrl = $commission->getAvatarUrl();

        $this->assertNull($avatarUrl);
    }

    public function test_commission_credit_note_relationship(): void
    {
        $creditNotePosition = OrderPosition::factory()->create([
            'order_id' => $this->order->id,
            'client_id' => $this->dbClient->id,
        ]);

        $commission = Commission::factory()->create([
            'user_id' => $this->agent->id,
            'order_position_id' => $this->orderPosition->id,
            'credit_note_order_position_id' => $creditNotePosition->id,
        ]);

        $this->assertInstanceOf(OrderPosition::class, $commission->creditNoteOrderPosition);
        $this->assertEquals($creditNotePosition->id, $commission->creditNoteOrderPosition->id);
    }

    public function test_commission_description_method(): void
    {
        $commission = $this->commissions[0];

        $description = $commission->getDescription();

        $this->assertIsString($description);
        $this->assertStringContainsString('5%', $description);
    }

    public function test_commission_rate_casting(): void
    {
        $commission = $this->commissions[0];

        $this->assertIsArray($commission->commission_rate);
        $this->assertArrayHasKey('commission_rate', $commission->commission_rate);
        $this->assertArrayHasKey('rate_type', $commission->commission_rate);
    }

    public function test_commission_relationships(): void
    {
        $commission = $this->commissions[0];

        $this->assertInstanceOf(User::class, $commission->user);
        $this->assertEquals($this->agent->id, $commission->user->id);

        $this->assertInstanceOf(OrderPosition::class, $commission->orderPosition);
        $this->assertEquals($this->orderPosition->id, $commission->orderPosition->id);
    }

    public function test_create_commission(): void
    {
        $commission = [
            'user_id' => $this->agent->id,
            'order_position_id' => $this->orderPosition->id,
            'commission_rate' => 0.08,
            'total_net_price' => 100.00,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/commissions', $commission);
        $response->assertStatus(201);

        $responseCommission = json_decode($response->getContent())->data;
        $dbCommission = Commission::query()
            ->whereKey($responseCommission->id)
            ->first();

        $this->assertNotEmpty($dbCommission);
        $this->assertEquals($commission['user_id'], $dbCommission->user_id);
        $this->assertEquals($commission['order_position_id'], $dbCommission->order_position_id);
        $this->assertEquals($commission['commission_rate'], $dbCommission->commission_rate['commission_rate']);
        $this->assertEquals($commission['total_net_price'], $dbCommission->total_net_price);
        $this->assertTrue($this->user->is($dbCommission->getCreatedBy()));
        $this->assertTrue($this->user->is($dbCommission->getUpdatedBy()));
    }

    public function test_create_commission_validation_fails(): void
    {
        $commission = [
            'user_id' => 999999,
            'order_position_id' => 999999,
            'total_net_price' => -100.00,
        ];

        $this->user->givePermissionTo($this->permissions['create']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->post('/api/commissions', $commission);
        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'user_id',
            'order_position_id',
        ]);
    }

    public function test_delete_commission(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/commissions/' . $this->commissions[0]->id);
        $response->assertStatus(204);

        $commission = $this->commissions[0]->fresh();
        $this->assertNotNull($commission->deleted_at);
        $this->assertTrue($this->user->is($commission->getDeletedBy()));
    }

    public function test_delete_commission_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['delete']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->delete('/api/commissions/' . (Commission::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_get_commission(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/commissions/' . $this->commissions[0]->id);
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCommission = $json->data;

        $this->assertNotEmpty($jsonCommission);
        $this->assertEquals($this->commissions[0]->id, $jsonCommission->id);
        $this->assertEquals($this->commissions[0]->user_id, $jsonCommission->user_id);
        $this->assertEquals($this->commissions[0]->order_position_id, $jsonCommission->order_position_id);
        $this->assertEquals($this->commissions[0]->commission_rate, (array) $jsonCommission->commission_rate);
        $this->assertEquals($this->commissions[0]->total_net_price, $jsonCommission->total_net_price);
        $this->assertEquals($this->commissions[0]->commission, $jsonCommission->commission);
    }

    public function test_get_commission_not_found(): void
    {
        $this->user->givePermissionTo($this->permissions['show']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/commissions/' . (Commission::max('id') + 1));
        $response->assertStatus(404);
    }

    public function test_get_commissions(): void
    {
        $this->user->givePermissionTo($this->permissions['index']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->get('/api/commissions');
        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $jsonCommissions = collect($json->data->data);

        $this->assertGreaterThanOrEqual(3, count($jsonCommissions));

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
    }

    public function test_update_commission(): void
    {
        $commission = [
            'id' => $this->commissions[0]->id,
            'commission' => 15.00,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->put('/api/commissions', $commission);
        $response->assertStatus(200);

        $responseCommission = json_decode($response->getContent())->data;
        $dbCommission = Commission::query()
            ->whereKey($responseCommission->id)
            ->first();

        $this->assertNotEmpty($dbCommission);
        $this->assertEquals($commission['id'], $dbCommission->id);
        $this->assertEquals($commission['commission'], $dbCommission->commission);
        $this->assertTrue($this->user->is($dbCommission->getUpdatedBy()));
    }

    public function test_update_commission_maximum(): void
    {
        $commission = [
            'id' => $this->commissions[1]->id,
            'commission' => 36.00,
        ];

        $this->user->givePermissionTo($this->permissions['update']);
        Sanctum::actingAs($this->user, ['user']);

        $response = $this->put('/api/commissions', $commission);
        $response->assertStatus(200);

        $responseCommission = json_decode($response->getContent())->data;
        $dbCommission = Commission::query()
            ->whereKey($responseCommission->id)
            ->first();

        $this->assertNotEmpty($dbCommission);
        $this->assertEquals($commission['commission'], $dbCommission->commission);
        $this->assertTrue($this->user->is($dbCommission->getUpdatedBy()));
    }
}
