<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Widgets\Project\Revenue;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Project;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->dbTenant, relationship: 'tenants')
        ->create();
    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);
    $this->makeOrder = fn (OrderTypeEnum $orderTypeEnum, array $attributes = []): Order => Order::factory()
        ->create(array_merge(
            [
                'address_invoice_id' => $this->address->getKey(),
                'contact_id' => $this->contact->getKey(),
                'currency_id' => Currency::default()->getKey(),
                'language_id' => $this->defaultLanguage->getKey(),
                'order_type_id' => OrderType::factory()
                    ->create(['order_type_enum' => $orderTypeEnum, 'is_active' => true])
                    ->getKey(),
                'payment_type_id' => PaymentType::default()->getKey(),
                'price_list_id' => PriceList::default()->getKey(),
                'tenant_id' => $this->dbTenant->getKey(),
            ],
            $attributes
        ));
});

test('renders successfully', function (): void {
    Livewire::test(Revenue::class)
        ->assertOk();
});

test('sums the net revenue of invoiced sales orders of the project', function (): void {
    $mainOrder = ($this->makeOrder)(OrderTypeEnum::Order, ['invoice_date' => now(), 'invoice_number' => 'RE-MAIN']);
    ($this->makeOrder)(
        OrderTypeEnum::SplitOrder,
        ['parent_id' => $mainOrder->getKey(), 'invoice_date' => now(), 'invoice_number' => 'RE-SPLIT']
    );
    ($this->makeOrder)(OrderTypeEnum::Order, ['parent_id' => $mainOrder->getKey()]);
    ($this->makeOrder)(
        OrderTypeEnum::Purchase,
        ['parent_id' => $mainOrder->getKey(), 'invoice_date' => now(), 'invoice_number' => 'ER-1']
    );
    $supplementaryOrder = ($this->makeOrder)(
        OrderTypeEnum::Order,
        ['invoice_date' => now(), 'invoice_number' => 'RE-SUPPLEMENT']
    );
    ($this->makeOrder)(OrderTypeEnum::Order, ['invoice_date' => now(), 'invoice_number' => 'RE-OTHER']);

    Order::query()
        ->update(['total_net_price' => 100]);
    Order::query()
        ->whereKey($mainOrder->getKey())
        ->update(['total_net_price' => 1000]);

    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $this->contact->getKey(),
        'order_id' => $mainOrder->getKey(),
    ]);
    $project->supplementaryOrders()
        ->attach($supplementaryOrder->getKey());

    Livewire::test(Revenue::class, ['projectId' => $project->getKey()])
        ->assertSet('sum', '1,200.00 ' . Currency::default()->symbol);
});

test('returns zero when project has no order', function (): void {
    ($this->makeOrder)(
        OrderTypeEnum::Order,
        ['invoice_date' => now(), 'invoice_number' => 'RE-OTHER', 'total_net_price' => 100]
    );

    $project = Project::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'order_id' => null,
    ]);

    Livewire::test(Revenue::class, ['projectId' => $project->getKey()])
        ->assertSet('sum', '0.00 ' . Currency::default()->symbol);
});
