<?php

uses(FluxErp\Tests\Livewire\PortalBaseSetup::class);
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Portal\Shop\Checkout;
use FluxErp\Mail\Order\OrderConfirmation;
use FluxErp\Models\Address;
use FluxErp\Models\Currency;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    PriceList::factory()->create([
        'is_default' => true,
    ]);
    Currency::factory()->create([
        'is_default' => true,
    ]);

    $this->orderType = OrderType::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
    ]);
    $this->paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbClient, relationship: 'clients')
        ->create([
            'is_active' => true,
            'is_sales' => true,
            'is_default' => true,
        ]);
});

test('can change delivery address', function (): void {
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
});

test('can create order', function (): void {
    $mail = Mail::fake();

    Livewire::actingAs($this->address)
        ->test(Checkout::class)
        ->set('termsAndConditions', true)
        ->set('commission', $commission = Str::uuid())
        ->call('buy')
        ->assertHasNoErrors()
        ->assertRedirect(route('portal.checkout-finish'));

    $this->assertDatabaseHas('orders', [
        'client_id' => $this->dbClient->getKey(),
        'contact_id' => $this->address->contact_id,
        'address_invoice_id' => $this->address->id,
        'address_delivery_id' => $this->address->id,
        'order_type_id' => $this->orderType->id,
        'payment_type_id' => $this->paymentType->id,
        'commission' => $commission,
    ]);

    Mail::assertQueued(OrderConfirmation::class, function (OrderConfirmation $mail) {
        return $mail->hasTo($this->address->email);
    });
    Mail::assertQueuedCount(1);

    $mailable = Mail::queued(OrderConfirmation::class)->first();
    $mail->sendNow($mailable);

    // Assert the mail was sent
    Mail::assertSent(OrderConfirmation::class, function (OrderConfirmation $mail) {
        return $mail->hasTo($this->address->email);
    });
});

test('cant create order without legal accepted', function (): void {
    Livewire::actingAs($this->address)
        ->test(Checkout::class)
        ->set('termsAndConditions', false)
        ->call('buy')
        ->assertHasNoErrors(['terms_and_conditions'])
        ->assertToastNotification(type: 'error');
});

test('renders successfully', function (): void {
    Livewire::actingAs($this->address)
        ->test(Checkout::class)
        ->assertStatus(200);
});
