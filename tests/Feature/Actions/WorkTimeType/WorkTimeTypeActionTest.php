<?php

use FluxErp\Actions\WorkTimeType\CreateWorkTimeType;
use FluxErp\Actions\WorkTimeType\DeleteWorkTimeType;
use FluxErp\Actions\WorkTimeType\UpdateWorkTimeType;
use FluxErp\Models\WorkTimeType;

test('create work time type', function (): void {
    $type = CreateWorkTimeType::make([
        'name' => 'Entwicklung',
        'is_billable' => true,
    ])->validate()->execute();

    expect($type)->toBeInstanceOf(WorkTimeType::class)
        ->name->toBe('Entwicklung');
});

test('create work time type requires name', function (): void {
    CreateWorkTimeType::assertValidationErrors([], 'name');
});

test('update work time type', function (): void {
    $type = WorkTimeType::factory()->create();

    $updated = UpdateWorkTimeType::make([
        'id' => $type->getKey(),
        'name' => 'Support',
    ])->validate()->execute();

    expect($updated->name)->toBe('Support');
});

test('delete work time type', function (): void {
    $type = WorkTimeType::factory()->create();

    expect(DeleteWorkTimeType::make(['id' => $type->getKey()])
        ->validate()->execute())->toBeTrue();
});
