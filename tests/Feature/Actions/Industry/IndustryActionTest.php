<?php

use FluxErp\Actions\Industry\CreateIndustry;
use FluxErp\Actions\Industry\DeleteIndustry;
use FluxErp\Actions\Industry\UpdateIndustry;
use FluxErp\Models\Industry;

test('create industry', function (): void {
    $industry = CreateIndustry::make(['name' => 'Software'])
        ->validate()->execute();

    expect($industry)->toBeInstanceOf(Industry::class)
        ->name->toBe('Software');
});

test('create industry requires name', function (): void {
    CreateIndustry::assertValidationErrors([], 'name');
});

test('update industry', function (): void {
    $industry = Industry::factory()->create();

    $updated = UpdateIndustry::make([
        'id' => $industry->getKey(),
        'name' => 'Updated',
    ])->validate()->execute();

    expect($updated->name)->toBe('Updated');
});

test('delete industry', function (): void {
    $industry = Industry::factory()->create();

    expect(DeleteIndustry::make(['id' => $industry->getKey()])
        ->validate()->execute())->toBeTrue();
});
