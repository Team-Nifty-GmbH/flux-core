<?php

namespace FluxErp\Tests\Feature\Web;

use FluxErp\Enums\OrderTypeEnum;
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
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderPositionsTest extends BaseSetup
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->id,
        ]);

        $address = Address::factory()->create([
            'client_id' => $this->dbClient->id,
            'contact_id' => $contact->id,
        ]);

        $priceList = PriceList::factory()->create();

        $currency = Currency::factory()->create([
            'is_default' => true,
        ]);

        $language = Language::factory()->create();

        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create([
                'is_default' => false,
            ]);

        $order = Order::factory()->create([
            'client_id' => $this->dbClient->id,
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'address_invoice_id' => $address->id,
            'address_delivery_id' => $address->id,
            'is_locked' => false,
        ]);

        OrderPosition::factory()->create([
            'client_id' => $this->dbClient->id,
            'order_id' => $order->id,
        ]);
    }

    public function test_order_positions_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('orders.order-positions.list.get', 'web'));

        $this->actingAs($this->user, 'web')->get('/orders/order-positions/list')
            ->assertStatus(200);
    }

    public function test_order_positions_no_user()
    {
        $this->get('/orders/order-positions/list')
            ->assertStatus(302)
            ->assertRedirect(route('login'));
    }

    public function test_order_positions_without_permission()
    {
        Permission::findOrCreate('orders.order-positions.list.get', 'web');

        $this->actingAs($this->user, 'web')->get('/orders/order-positions/list')
            ->assertStatus(403);
    }
}
