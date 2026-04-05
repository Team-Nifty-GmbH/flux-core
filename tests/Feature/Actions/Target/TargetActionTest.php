<?php

use FluxErp\Actions\Target\CreateTarget;
use FluxErp\Actions\Target\DeleteTarget;
use FluxErp\Actions\Target\UpdateTarget;

test('create target for order positions', function (): void {
    $target = CreateTarget::make([
        'name' => 'Monthly Revenue',
        'target_value' => 50000,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'model_type' => morph_alias(FluxErp\Models\OrderPosition::class),
        'timeframe_column' => 'created_at',
        'aggregate_type' => 'sum',
        'aggregate_column' => 'total_net_price',
        'owner_column' => 'created_by',
    ])->validate()->execute();

    expect($target)->name->toBe('Monthly Revenue');
});

test('create target requires all fields', function (): void {
    CreateTarget::assertValidationErrors([], [
        'name', 'target_value', 'start_date', 'end_date', 'model_type',
        'timeframe_column', 'aggregate_type', 'aggregate_column',
    ]);
});

test('create target rejects invalid timeframe column', function (): void {
    expect(fn () => CreateTarget::make([
        'name' => 'Bad Target',
        'target_value' => 100,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'model_type' => morph_alias(FluxErp\Models\OrderPosition::class),
        'timeframe_column' => 'nonexistent_column',
        'aggregate_type' => 'sum',
        'aggregate_column' => 'total_net_price',
    ])->validate())->toThrow(Illuminate\Validation\ValidationException::class);
});

test('update target', function (): void {
    $target = CreateTarget::make([
        'name' => 'Original',
        'target_value' => 10000,
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-30',
        'model_type' => morph_alias(FluxErp\Models\OrderPosition::class),
        'timeframe_column' => 'created_at',
        'aggregate_type' => 'count',
        'aggregate_column' => 'id',
        'owner_column' => 'created_by',
    ])->validate()->execute();

    $updated = UpdateTarget::make([
        'id' => $target->getKey(),
        'name' => 'Updated Target',
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-30',
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated Target');
});

test('delete target', function (): void {
    $target = CreateTarget::make([
        'name' => 'Temp',
        'target_value' => 1000,
        'start_date' => '2026-01-01',
        'end_date' => '2026-03-31',
        'model_type' => morph_alias(FluxErp\Models\OrderPosition::class),
        'timeframe_column' => 'created_at',
        'aggregate_type' => 'sum',
        'aggregate_column' => 'amount',
        'owner_column' => 'created_by',
    ])->validate()->execute();

    expect(DeleteTarget::make(['id' => $target->getKey()])
        ->validate()->execute())->toBeTrue();
});
