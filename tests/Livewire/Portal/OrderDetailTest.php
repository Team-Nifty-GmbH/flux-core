<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Portal\OrderDetail;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

beforeEach(function (): void {
    $contact = Contact::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->id,
    ]);

    $currency = Currency::factory()->create();

    $language = Language::factory()->create();

    $orderType = OrderType::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();

    $priceList = PriceList::factory()->create();

    $this->orders = Order::factory()
        ->count(1)
        ->hasOrderPositions(
            1,
            function ($attributes, $order) {
                return [
                    'tenant_id' => $order->tenant_id,
                    'name' => 'test orderposition',
                ];
            }
        )
        ->create([
            'tenant_id' => $this->dbTenant->getKey(),
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'contact_id' => $this->contact->id,
            'address_invoice_id' => $this->address->id,
            'address_delivery_id' => $this->address->id,
            'is_locked' => true,
        ]);

    $this->orders[] = Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'language_id' => $language->id,
        'order_type_id' => $orderType->id,
        'payment_type_id' => $paymentType->id,
        'price_list_id' => $priceList->id,
        'currency_id' => $currency->id,
        'contact_id' => $contact->id,
        'address_invoice_id' => $address->id,
        'address_delivery_id' => $address->id,
        'is_locked' => true,
    ]);

    Order::addGlobalScope('portal', function ($query): void {
        $query->where('contact_id', auth()->user()->contact_id)
            ->where(fn ($query) => $query->where('is_locked', true)
                ->orWhere('is_imported', true)
            );
    });

    $this->be($this->address, 'address');
});

test('dont render order from other address', function (): void {
    Livewire::test(OrderDetail::class, ['id' => $this->orders[1]->id])
        ->assertNotFound();
});

test('renders successfully', function (): void {
    Livewire::test(OrderDetail::class, ['id' => $this->orders[0]->id])
        ->assertOk();
});

test('select order position', function (): void {
    Livewire::test(OrderDetail::class, ['id' => $this->orders[0]->id])
        ->call('selectPosition', $this->orders[0]->orderPositions->first()->id)
        ->assertSet('positionDetails.id', $this->orders[0]->orderPositions->first()->id);
});
