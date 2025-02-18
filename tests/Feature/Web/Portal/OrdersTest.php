<?php

namespace FluxErp\Tests\Feature\Web\Portal;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\Permission;
use FluxErp\Models\PriceList;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrdersTest extends PortalSetup
{
    use DatabaseTransactions;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $priceList = PriceList::factory()->create([
            'is_default' => true,
        ]);

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
                'is_default' => true,
                'is_active' => true,
                'is_sales' => true,
            ]);

        $this->order = Order::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'address_invoice_id' => $this->user->id,
            'address_delivery_id' => $this->user->id,
            'is_locked' => true,
        ]);
    }

    public function test_portal_orders_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('orders.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.orders'))
            ->assertStatus(200);
    }

    public function test_portal_orders_no_user()
    {
        $this->get(route('portal.orders'))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_orders_without_permission()
    {
        Permission::findOrCreate('orders.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.orders'))
            ->assertStatus(403);
    }

    public function test_portal_orders_id_page()
    {
        $this->user->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.orders.id', ['id' => $this->order->id]))
            ->assertStatus(200);
    }

    public function test_portal_orders_id_no_user()
    {
        $this->get(route('portal.orders.id', ['id' => $this->order->id]))
            ->assertStatus(302)
            ->assertRedirect($this->portalDomain . '/login');
    }

    public function test_portal_orders_id_without_permission()
    {
        Permission::findOrCreate('orders.{id}.get', 'address');

        $this->actingAs($this->user, 'address')->get(route('portal.orders.id', ['id' => $this->order->id]))
            ->assertStatus(403);
    }

    public function test_portal_orders_id_order_not_found()
    {
        $this->order->delete();

        $this->user->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.orders.id', ['id' => $this->order->id]))
            ->assertStatus(404);
    }

    public function test_portal_orders_id_order_not_locked()
    {
        $this->order->update(['is_locked' => false, 'is_imported' => false]);

        $this->user->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.orders.id', ['id' => $this->order->id]))
            ->assertStatus(404);
    }

    public function test_portal_orders_id_order_not_contact_id()
    {
        $this->order->update(['contact_id' => null]);

        $this->user->givePermissionTo(Permission::findOrCreate('orders.{id}.get', 'address'));

        $this->actingAs($this->user, 'address')->get(route('portal.orders.id', ['id' => $this->order->id]))
            ->assertStatus(404);
    }
}
