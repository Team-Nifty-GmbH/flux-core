<?php

use FluxErp\Livewire\Settings\WorkTimeTypes;
use FluxErp\Models\WorkTimeType;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->workTimeType = WorkTimeType::factory()->create([
        'name' => 'Test Type',
        'is_billable' => true,
    ]);
});

test('can inline edit row', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', $this->workTimeType->getKey())
        ->assertSet('inlineEditingId', $this->workTimeType->getKey())
        ->assertSet('workTimeTypeForm.name', 'Test Type')
        ->assertSet('workTimeTypeForm.is_billable', true);
});

test('can save inline changes', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', $this->workTimeType->getKey())
        ->set('workTimeTypeForm.name', 'Updated Type')
        ->call('saveInline')
        ->assertHasNoErrors();

    $this->workTimeType->refresh();
    expect($this->workTimeType->name)->toBe('Updated Type');
});

test('can cancel inline edit', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', $this->workTimeType->getKey())
        ->set('workTimeTypeForm.name', 'Changed Name')
        ->call('cancelInline')
        ->assertSet('inlineEditingId', null);

    $this->workTimeType->refresh();
    expect($this->workTimeType->name)->toBe('Test Type');
});

test('save inline validates', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', $this->workTimeType->getKey())
        ->set('workTimeTypeForm.name', '')
        ->call('saveInline');

    $this->workTimeType->refresh();
    expect($this->workTimeType->name)->toBe('Test Type');
});
