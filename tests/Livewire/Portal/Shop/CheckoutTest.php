<?php

namespace FluxErp\Tests\Livewire\Portal\Shop;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Portal\Shop\Checkout;
use FluxErp\Models\Address;
use FluxErp\Models\Currency;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Livewire;

class CheckoutTest extends BaseSetup
{
    private OrderType $orderType;

    private PaymentType $paymentType;

    public function setUp(): void
    {
        parent::setUp();

        PriceList::factory()->create([
            'is_default' => true,
        ]);
        Currency::factory()->create([
            'is_default' => true,
        ]);

        $this->orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->id,
            'order_type_enum' => OrderTypeEnum::Order,
            'is_active' => true,
        ]);
        $this->paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
            ->create([
                'client_id' => $this->dbClient->id,
                'is_active' => true,
                'is_sales' => true,
                'is_default' => true,
            ]);
    }

    public function test_renders_successfully()
    {
        Livewire::actingAs($this->address)
            ->test(Checkout::class)
            ->assertStatus(200);
    }

    public function test_can_change_delivery_address()
    {
        $newAddress = Address::factory()->make();

        Livewire::actingAs($this->address)
            ->test(Checkout::class)
            ->set(Arr::prependKeysWith($newAddress->toArray(), 'address.'))
            ->call('saveDeliveryAddress')
            ->assertReturned(true)
            ->assertSet('deliveryAddress.id', null)
            ->assertSet('deliveryAddress.client_id', null)
            ->assertSet('deliveryAddress.language_id', null)
            ->assertSet('deliveryAddress.country_id', $newAddress->country_id)
            ->assertSet('deliveryAddress.contact_id', null)
            ->assertSet('deliveryAddress.company', $newAddress->company)
            ->assertSet('deliveryAddress.title', $newAddress->title)
            ->assertSet('deliveryAddress.salutation', $newAddress->salutation)
            ->assertSet('deliveryAddress.firstname', $newAddress->firstname)
            ->assertSet('deliveryAddress.lastname', $newAddress->lastname)
            ->assertSet('deliveryAddress.name', null)
            ->assertSet('deliveryAddress.addition', $newAddress->addition)
            ->assertSet('deliveryAddress.mailbox', $newAddress->mailbox)
            ->assertSet('deliveryAddress.mailbox_city', $newAddress->mailbox_city)
            ->assertSet('deliveryAddress.mailbox_zip', $newAddress->mailbox_zip)
            ->assertSet('deliveryAddress.latitude', $newAddress->latitude)
            ->assertSet('deliveryAddress.longitude', $newAddress->longitude)
            ->assertSet('deliveryAddress.zip', $newAddress->zip)
            ->assertSet('deliveryAddress.city', $newAddress->city)
            ->assertSet('deliveryAddress.street', $newAddress->street)
            ->assertSet('deliveryAddress.url', $newAddress->url)
            ->assertSet('deliveryAddress.email_primary', $newAddress->email_primary)
            ->assertSet('deliveryAddress.phone', $newAddress->phone)
            ->assertSet('deliveryAddress.department', $newAddress->department)
            ->assertSet('deliveryAddress.email', $newAddress->email)
            ->assertSee($newAddress->postal_address, false);
    }

    public function test_cant_create_order_without_legal_accepted()
    {
        Livewire::actingAs($this->address)
            ->test(Checkout::class)
            ->set('termsAndConditions', false)
            ->call('buy')
            ->assertHasNoErrors(['terms_and_conditions'])
            ->assertWireuiNotification(icon: 'error');
    }

    public function test_can_create_order()
    {
        Livewire::actingAs($this->address)
            ->test(Checkout::class)
            ->set('termsAndConditions', true)
            ->set('commission', $commission = Str::uuid())
            ->call('buy')
            ->assertHasNoErrors()
            ->assertRedirect(route('portal.checkout-finish'));

        $this->assertDatabaseHas('orders', [
            'client_id' => $this->dbClient->id,
            'contact_id' => $this->address->contact_id,
            'address_invoice_id' => $this->address->id,
            'address_delivery_id' => $this->address->id,
            'order_type_id' => $this->orderType->id,
            'payment_type_id' => $this->paymentType->id,
            'commission' => $commission,
        ]);
    }
}
