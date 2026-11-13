<?php

use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Accounting\FixedAssets;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $this->assetAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Asset,
    ]);
    $this->expenseAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Expense,
    ]);
});

function fixedAssetOrderPosition(OrderTypeEnum $orderTypeEnum): OrderPosition
{
    $order = Order::factory()->create([
        'order_type_id' => OrderType::factory()
            ->create(['order_type_enum' => $orderTypeEnum, 'is_active' => true])
            ->getKey(),
        'address_invoice_id' => Address::factory()
            ->create(['contact_id' => test()->contact->getKey(), 'is_main_address' => true])
            ->getKey(),
        'contact_id' => test()->contact->getKey(),
        'payment_type_id' => PaymentType::factory()
            ->hasAttached(test()->dbTenant, relationship: 'tenants')
            ->create()
            ->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'tenant_id' => test()->dbTenant->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'language_id' => test()->defaultLanguage->getKey(),
        'invoice_date' => '2026-03-20',
        'system_delivery_date' => '2026-03-18',
    ]);

    return OrderPosition::factory()->create([
        'order_id' => $order->getKey(),
        'tenant_id' => test()->dbTenant->getKey(),
        'ledger_account_id' => test()->assetAccount->getKey(),
        'name' => 'Delivery van',
        'total_net_price' => -36000,
    ]);
}

test('renders successfully', function (): void {
    Livewire::test(FixedAssets::class)
        ->assertOk();
});

test('edit resets the form and opens the modal', function (): void {
    Livewire::test(FixedAssets::class)
        ->call('edit')
        ->assertHasNoErrors()
        ->assertSet('fixedAsset.id', null)
        ->assertOpensModal('edit-fixed-asset-modal');
});

test('can create a fixed asset with its schedule', function (): void {
    Livewire::test(FixedAssets::class)
        ->call('edit')
        ->set('fixedAsset.name', 'Delivery van')
        ->set('fixedAsset.ledger_account_id', $this->assetAccount->getKey())
        ->set('fixedAsset.depreciation_ledger_account_id', $this->expenseAccount->getKey())
        ->set('fixedAsset.acquired_at', '2026-03-20')
        ->set('fixedAsset.acquisition_cost', 36000)
        ->set('fixedAsset.useful_life_months', 72)
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect();

    expect(FixedAsset::query()->sole()->depreciations()->count())->toBe(72);
});

test('prefills the form from a purchase order position', function (): void {
    $orderPosition = fixedAssetOrderPosition(OrderTypeEnum::Purchase);

    Livewire::test(FixedAssets::class)
        ->call('edit')
        ->set('fixedAsset.order_position_id', $orderPosition->getKey())
        ->call('changedOrderPosition', $orderPosition->getKey())
        ->assertSet('fixedAsset.order_id', $orderPosition->order_id)
        ->assertSet('fixedAsset.contact_id', $this->contact->getKey())
        ->assertSet('fixedAsset.ledger_account_id', $this->assetAccount->getKey())
        ->assertSet('fixedAsset.name', 'Delivery van')
        ->assertSet('fixedAsset.acquisition_cost', 36000.0)
        ->assertSet('fixedAsset.acquired_at', '2026-03-18');
});

test('rejects a position of a sales order', function (): void {
    $orderPosition = fixedAssetOrderPosition(OrderTypeEnum::Order);

    Livewire::test(FixedAssets::class)
        ->call('edit')
        ->set('fixedAsset.order_position_id', $orderPosition->getKey())
        ->call('changedOrderPosition', $orderPosition->getKey())
        ->assertSet('fixedAsset.order_position_id', null)
        ->assertSet('fixedAsset.name', null);
});
