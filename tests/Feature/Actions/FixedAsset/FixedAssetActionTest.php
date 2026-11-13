<?php

use FluxErp\Actions\FixedAsset\BookFixedAssetDepreciations;
use FluxErp\Actions\FixedAsset\CreateFixedAsset;
use FluxErp\Actions\FixedAsset\DeleteFixedAsset;
use FluxErp\Actions\FixedAsset\UpdateFixedAsset;
use FluxErp\Enums\LedgerAccountTypeEnum;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\FixedAssetDepreciation;
use FluxErp\Models\Language;
use FluxErp\Models\LedgerAccount;
use FluxErp\Models\LedgerBooking;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->assetAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Asset,
    ]);
    $this->expenseAccount = LedgerAccount::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Expense,
    ]);
});

function baseFixedAssetData(array $overrides = []): array
{
    return array_merge([
        'ledger_account_id' => test()->assetAccount->getKey(),
        'depreciation_ledger_account_id' => test()->expenseAccount->getKey(),
        'name' => 'Delivery van',
        'acquired_at' => '2026-03-20',
        'acquisition_cost' => 36000,
        'useful_life_months' => 72,
    ], $overrides);
}

function fixedAssetOrder(OrderTypeEnum $orderTypeEnum): Order
{
    $contact = Contact::factory()->create();

    return Order::factory()->create([
        'address_invoice_id' => Address::factory()
            ->create(['contact_id' => $contact->getKey()])
            ->getKey(),
        'contact_id' => $contact->getKey(),
        'tenant_id' => test()->dbTenant->getKey(),
        'language_id' => Language::default()->getKey(),
        'price_list_id' => PriceList::default()->getKey(),
        'payment_type_id' => PaymentType::default()->getKey(),
        'currency_id' => Currency::default()->getKey(),
        'order_type_id' => OrderType::factory()
            ->create(['order_type_enum' => $orderTypeEnum])
            ->getKey(),
    ]);
}

function bookFirstDepreciation(FixedAsset $fixedAsset): void
{
    BookFixedAssetDepreciations::make([
        'fixed_asset_id' => $fixedAsset->getKey(),
        'until' => $fixedAsset->depreciations()->min('booking_date'),
    ])
        ->validate()
        ->execute();
}

test('create fixed asset generates the depreciation schedule from the acquisition month', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData())->validate()->execute();

    $depreciations = $fixedAsset->depreciations()->orderBy('sequence')->get();

    expect($depreciations)->toHaveCount(72)
        ->and($depreciations->first()->booking_date->toDateString())->toBe('2026-03-31')
        ->and($depreciations->first()->amount)->toEqual(500)
        ->and($depreciations->last()->booking_date->toDateString())->toBe('2032-02-29')
        ->and($fixedAsset->depreciation_starts_at->toDateString())->toBe('2026-03-20')
        ->and($fixedAsset->tenant_id)->toBe($this->dbTenant->getKey())
        ->and($fixedAsset->residual_value)->toEqual(0)
        ->and($fixedAsset->book_value)->toEqual(36000);
});

test('create fixed asset rejects ledger accounts of the wrong type', function (): void {
    expect(fn () => CreateFixedAsset::make(baseFixedAssetData([
        'ledger_account_id' => $this->expenseAccount->getKey(),
        'depreciation_ledger_account_id' => $this->assetAccount->getKey(),
    ]))->validate()->execute())
        ->toThrow(ValidationException::class);
});

test('create fixed asset rejects a ledger account of a foreign tenant', function (): void {
    $foreignAccount = LedgerAccount::factory()->create([
        'tenant_id' => Tenant::factory()->create()->getKey(),
        'ledger_account_type_enum' => LedgerAccountTypeEnum::Asset,
    ]);

    CreateFixedAsset::make(baseFixedAssetData(['ledger_account_id' => $foreignAccount->getKey()]))
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('create fixed asset rejects a residual value not below the acquisition cost', function (): void {
    CreateFixedAsset::make(baseFixedAssetData(['residual_value' => 36000]))
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('create fixed asset rejects a depreciation start before the acquisition', function (): void {
    CreateFixedAsset::make(baseFixedAssetData(['depreciation_starts_at' => '2026-02-01']))
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('create fixed asset rejects an amount too small for the useful life', function (): void {
    CreateFixedAsset::make(baseFixedAssetData(['acquisition_cost' => '0.10', 'useful_life_months' => 36]))
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('update fixed asset regenerates the schedule while nothing is booked', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData())->validate()->execute();

    UpdateFixedAsset::make([
        'id' => $fixedAsset->getKey(),
        'useful_life_months' => 36,
        'residual_value' => 1,
    ])
        ->validate()
        ->execute();

    $depreciations = $fixedAsset->depreciations()->orderBy('sequence')->get();

    expect($depreciations)->toHaveCount(36)
        ->and($depreciations->first()->amount)->toEqual('999.97')
        ->and($depreciations->last()->book_value_after)->toEqual(1);
});

test('update fixed asset keeps the schedule when unchanged values are sent', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData())->validate()->execute();
    bookFirstDepreciation($fixedAsset);

    $updated = UpdateFixedAsset::make(array_merge(
        baseFixedAssetData(['name' => 'Renamed van']),
        ['id' => $fixedAsset->getKey(), 'depreciation_starts_at' => '2026-03-20', 'residual_value' => 0]
    ))
        ->validate()
        ->execute();

    expect($updated->name)->toBe('Renamed van')
        ->and($fixedAsset->depreciations()->count())->toBe(72);
});

test('update fixed asset rejects schedule changes once a depreciation is booked', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData())->validate()->execute();
    bookFirstDepreciation($fixedAsset);

    UpdateFixedAsset::make(['id' => $fixedAsset->getKey(), 'useful_life_months' => 36])
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('the stored book value follows the depreciation bookings', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData())->validate()->execute();
    bookFirstDepreciation($fixedAsset);

    expect($fixedAsset->refresh()->book_value)->toEqual(35500);

    LedgerBooking::query()
        ->where('source_type', morph_alias(FixedAssetDepreciation::class))
        ->first()
        ->delete();

    expect($fixedAsset->refresh()->book_value)->toEqual(36000);
});

test('depreciations up to the externally depreciated month count towards the book value', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData(['externally_depreciated_until' => '2026-05-31']))
        ->validate()
        ->execute();

    expect($fixedAsset->book_value)->toEqual(34500)
        ->and($fixedAsset->hasBookedDepreciations())->toBeFalse();

    $updated = UpdateFixedAsset::make(['id' => $fixedAsset->getKey(), 'externally_depreciated_until' => null])
        ->validate()
        ->execute();

    expect($updated->book_value)->toEqual(36000);
});

test('create fixed asset rejects invalid input', function (array $overrides): void {
    CreateFixedAsset::make(baseFixedAssetData($overrides))
        ->validate()
        ->execute();
})
    ->with([
        'more than two decimals' => [['acquisition_cost' => '1000.009']],
        'useful life above 100 years' => [['useful_life_months' => 1201]],
        'order position without order' => [['order_position_id' => 1]],
        'externally depreciated in the future' => [['externally_depreciated_until' => '2099-12-31']],
    ])
    ->throws(ValidationException::class);

test('create fixed asset accepts a purchase order', function (): void {
    $order = fixedAssetOrder(OrderTypeEnum::Purchase);

    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData(['order_id' => $order->getKey()]))
        ->validate()
        ->execute();

    expect($fixedAsset->order_id)->toBe($order->getKey());
});

test('create fixed asset rejects an order that is not a purchase', function (): void {
    CreateFixedAsset::make(baseFixedAssetData(['order_id' => fixedAssetOrder(OrderTypeEnum::Order)->getKey()]))
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('update fixed asset locks the externally depreciated month once a depreciation is booked', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData(['externally_depreciated_until' => '2026-04-30']))
        ->validate()
        ->execute();
    bookFirstDepreciation($fixedAsset);

    UpdateFixedAsset::make([
        'id' => $fixedAsset->getKey(),
        'externally_depreciated_until' => '2026-03-31',
    ])
        ->validate()
        ->execute();
})->throws(ValidationException::class);

test('update fixed asset keeps an unchanged externally depreciated month after bookings', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData(['externally_depreciated_until' => '2026-04-30']))
        ->validate()
        ->execute();
    bookFirstDepreciation($fixedAsset);

    $updated = UpdateFixedAsset::make([
        'id' => $fixedAsset->getKey(),
        'name' => 'Renamed van',
        'externally_depreciated_until' => '2026-04-30',
    ])
        ->validate()
        ->execute();

    expect($updated->name)->toBe('Renamed van');
});

test('delete fixed asset removes an unbooked asset', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData())->validate()->execute();

    DeleteFixedAsset::make(['id' => $fixedAsset->getKey()])->validate()->execute();

    $this->assertSoftDeleted('fixed_assets', ['id' => $fixedAsset->getKey()]);
});

test('delete fixed asset rejects an asset with booked depreciations', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData())->validate()->execute();
    bookFirstDepreciation($fixedAsset);

    DeleteFixedAsset::make(['id' => $fixedAsset->getKey()])->validate()->execute();
})->throws(ValidationException::class);

test('update fixed asset leaves no stale schedule rows behind', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData())->validate()->execute();

    UpdateFixedAsset::make(['id' => $fixedAsset->getKey(), 'useful_life_months' => 36])
        ->validate()
        ->execute();

    expect($fixedAsset->depreciations()->withTrashed()->count())->toBe(36);
});

test('update fixed asset only checks the relations it changes', function (): void {
    $fixedAsset = CreateFixedAsset::make(baseFixedAssetData())->validate()->execute();
    $this->assetAccount->update(['ledger_account_type_enum' => LedgerAccountTypeEnum::Liability]);

    $updated = UpdateFixedAsset::make(['id' => $fixedAsset->getKey(), 'name' => 'Renamed van'])
        ->validate()
        ->execute();

    expect($updated->name)->toBe('Renamed van');
});
