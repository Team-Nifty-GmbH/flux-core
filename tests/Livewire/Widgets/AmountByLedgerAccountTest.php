<?php

use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\AmountByLedgerAccount;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->ledgerAccountRevenue = LedgerAccount::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'name' => 'TestLedgerAccountRevenue',
        'ledger_account_type_enum' => 'revenue',
    ]);

    $this->ledgerAccountExpenses = LedgerAccount::factory()->create([
        'client_id' => $this->dbClient->getKey(),
        'name' => 'TestLedgerAccountExpenses',
        'ledger_account_type_enum' => 'expense',
    ]);

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

    $this->orders = Order::factory()->count(5)->create([
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

    OrderPosition::factory()->count(10)->create([
        'client_id' => $this->dbClient->getKey(),
        'ledger_account_id' => $this->ledgerAccountRevenue->id,
        'order_id' => $this->orders[0]->id,
        'total_gross_price' => 2000,
    ]);

    OrderPosition::factory()->count(10)->create([
        'client_id' => $this->dbClient->getKey(),
        'ledger_account_id' => $this->ledgerAccountExpenses->id,
        'order_id' => $this->orders[1]->id,
        'total_gross_price' => 2000,
    ]);

    OrderPosition::factory()->count(10)->create([
        'client_id' => $this->dbClient->getKey(),
        'order_id' => $this->orders[2]->id,
        'total_gross_price' => 2000,
    ]);

    OrderPosition::factory()->count(2)->create([
        'client_id' => $this->dbClient->getKey(),
        'order_id' => $this->orders[3]->id,
        'total_gross_price' => 2000,
        'created_at' => Carbon::yesterday(),
    ]);
});

test('calculate chart returns right numbers timeframe today', function (): void {
    $timeFrame = TimeFrameEnum::Today;

    Livewire::test(AmountByLedgerAccount::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            __('Not Assigned'),
            $this->ledgerAccountRevenue->name,
            $this->ledgerAccountExpenses->name,
        ])
        ->assertSet('series', [
            round(
                $this->orders[0]
                    ->orderPositions()
                    ->sum('total_gross_price'),
                2
            ),
            round(
                $this->orders[1]
                    ->orderPositions()
                    ->sum('total_gross_price'),
                2
            ),
            round(
                $this->orders[2]
                    ->orderPositions()
                    ->sum('total_gross_price'),
                2
            ),
        ])
        ->assertHasNoErrors()
        ->assertOk();
});

test('calculate chart returns right numbers timeframe yesterday', function (): void {
    $timeFrame = TimeFrameEnum::Yesterday;

    Livewire::test(AmountByLedgerAccount::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            __('Not Assigned'),
        ])
        ->assertSet('series', [
            round(
                $this->orders[3]
                    ->orderPositions()
                    ->sum('total_gross_price'),
                2
            ),
        ])
        ->assertHasNoErrors()
        ->assertOk();
});

test('net orders get successfully ignored', function (): void {
    $timeFrame = TimeFrameEnum::Today;

    OrderPosition::factory()->count(2)->create([
        'client_id' => $this->dbClient->getKey(),
        'ledger_account_id' => $this->ledgerAccountExpenses->id,
        'order_id' => $this->orders[4]->id,
        'total_net_price' => 2000,
    ]);

    Livewire::test(AmountByLedgerAccount::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            __('Not Assigned'),
            $this->ledgerAccountRevenue->name,
            $this->ledgerAccountExpenses->name,
        ])
        ->assertSet('series', [
            round(
                $this->orders[0]
                    ->orderPositions()
                    ->sum('total_gross_price'),
                2
            ),
            round(
                $this->orders[1]
                    ->orderPositions()
                    ->sum('total_gross_price'),
                2
            ),
            round(
                $this->orders[2]
                    ->orderPositions()
                    ->sum('total_gross_price'),
                2
            ),
        ])
        ->assertHasNoErrors()
        ->assertOk();
});

test('renders successfully', function (): void {
    Livewire::test(AmountByLedgerAccount::class)
        ->assertOk();
});
