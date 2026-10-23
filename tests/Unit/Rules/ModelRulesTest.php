<?php

use FluxErp\Models\Currency;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\PurchaseInvoicePosition;
use FluxErp\Models\VatRate;
use FluxErp\Rules\ModelDoesntExist;
use FluxErp\Rules\ModelExists;
use Illuminate\Support\Facades\Validator;

test('ModelExists passes for existing model', function (): void {
    $currency = Currency::factory()->create();

    $passes = Validator::make(
        ['id' => $currency->getKey()],
        ['id' => app(ModelExists::class, ['model' => Currency::class])]
    )->passes();

    expect($passes)->toBeTrue();
});

test('ModelExists fails for non-existing model', function (): void {
    $nonExistingId = (Currency::query()->max('id') ?? 0) + 9999;

    $passes = Validator::make(
        ['id' => $nonExistingId],
        ['id' => app(ModelExists::class, ['model' => Currency::class])]
    )->passes();

    expect($passes)->toBeFalse();
});

test('ModelDoesntExist passes when model does not exist', function (): void {
    $nonExistingId = (Currency::query()->max('id') ?? 0) + 9999;

    $passes = Validator::make(
        ['id' => $nonExistingId],
        ['id' => app(ModelDoesntExist::class, ['model' => Currency::class])]
    )->passes();

    expect($passes)->toBeTrue();
});

test('ModelDoesntExist fails when model exists', function (): void {
    $currency = Currency::factory()->create();

    $passes = Validator::make(
        ['id' => $currency->getKey()],
        ['id' => app(ModelDoesntExist::class, ['model' => Currency::class])]
    )->passes();

    expect($passes)->toBeFalse();
});

test('ModelExists with subject keeps the unchanged value of a nested item', function (): void {
    $salesVatRate = VatRate::factory()->create(['is_purchase' => false]);
    $position = PurchaseInvoice::factory()->create()
        ->purchaseInvoicePositions()
        ->create([
            'amount' => 1,
            'unit_price' => 100,
            'total_price' => 100,
            'vat_rate_id' => $salesVatRate->getKey(),
        ]);

    $passes = Validator::make(
        ['positions' => [['id' => $position->getKey(), 'vat_rate_id' => $salesVatRate->getKey()]]],
        [
            'positions.*.vat_rate_id' => [
                app(ModelExists::class, ['model' => VatRate::class, 'subject' => PurchaseInvoicePosition::class])
                    ->where('is_purchase', true),
            ],
        ]
    )->passes();

    expect($passes)->toBeTrue();
});

test('ModelExists with subject checks a changed value of a nested item', function (): void {
    $position = PurchaseInvoice::factory()->create()
        ->purchaseInvoicePositions()
        ->create([
            'amount' => 1,
            'unit_price' => 100,
            'total_price' => 100,
            'vat_rate_id' => VatRate::factory()->create(['is_purchase' => true])->getKey(),
        ]);

    $passes = Validator::make(
        [
            'positions' => [
                [
                    'id' => $position->getKey(),
                    'vat_rate_id' => VatRate::factory()->create(['is_purchase' => false])->getKey(),
                ],
            ],
        ],
        [
            'positions.*.vat_rate_id' => [
                app(ModelExists::class, ['model' => VatRate::class, 'subject' => PurchaseInvoicePosition::class])
                    ->where('is_purchase', true),
            ],
        ]
    )->passes();

    expect($passes)->toBeFalse();
});
