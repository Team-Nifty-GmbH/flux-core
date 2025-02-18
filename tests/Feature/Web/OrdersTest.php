<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrdersTest extends BaseSetup
{
    use DatabaseTransactions;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $address = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
        ]);

        $priceList = PriceList::factory()->create();

        $currency = Currency::factory()->create([
            'is_default' => true,
        ]);

        $language = Language::factory()->create();

        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create([
                'is_default' => false,
            ]);

        $this->order = Order::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'address_invoice_id' => $address->id,
            'address_delivery_id' => $address->id,
            'is_locked' => false,
        ]);
    }

    public function test_orders_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('orders.list.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/orders/list')
            ->assertStatus(200);
    }

    public function test_orders_no_user()
    {
        $this->get('/orders/list')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_orders_without_permission()
    {
        Permission::findOrCreate('orders.list.get', 'web');

        $this->actingAs($this->user, 'web')->get('/orders/list')
            ->assertStatus(403);
    }

    public function test_orders_id_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/orders/' . $this->order->id)
            ->assertStatus(200);
    }

    public function test_orders_id_no_user()
    {
        $this->get('/orders/' . $this->order->id)
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_orders_id_without_permission()
    {
        Permission::findOrCreate('orders.{id}.get', 'web');

        $this->actingAs($this->user, 'web')->get('/orders/' . $this->order->id)
            ->assertStatus(403);
    }

    public function test_orders_id_order_not_found()
    {
        $this->order->delete();

        $this->user->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/orders/' . $this->order->id)
            ->assertStatus(404);
    }
}
