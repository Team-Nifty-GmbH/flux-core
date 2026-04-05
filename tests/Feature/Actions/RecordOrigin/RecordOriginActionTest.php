<?php

use FluxErp\Actions\RecordOrigin\CreateRecordOrigin;
use FluxErp\Actions\RecordOrigin\DeleteRecordOrigin;
use FluxErp\Actions\RecordOrigin\UpdateRecordOrigin;
use FluxErp\Models\RecordOrigin;

test('create record origin', function (): void {
    $origin = CreateRecordOrigin::make([
        'name' => 'Webshop',
        'model_type' => morph_alias(FluxErp\Models\Order::class),
    ])->validate()->execute();

    expect($origin)->toBeInstanceOf(RecordOrigin::class)
        ->name->toBe('Webshop');
});

test('create record origin requires name and model_type', function (): void {
    CreateRecordOrigin::assertValidationErrors([], ['name', 'model_type']);
});

test('update record origin', function (): void {
    $origin = RecordOrigin::factory()->create([
        'model_type' => morph_alias(FluxErp\Models\Order::class),
    ]);

    $updated = UpdateRecordOrigin::make([
        'id' => $origin->getKey(),
        'name' => 'Phone',
    ])->validate()->execute();

    expect($updated->name)->toBe('Phone');
});

test('delete record origin', function (): void {
    $origin = RecordOrigin::factory()->create([
        'model_type' => morph_alias(FluxErp\Models\Order::class),
    ]);

    expect(DeleteRecordOrigin::make(['id' => $origin->getKey()])
        ->validate()->execute())->toBeTrue();
});
