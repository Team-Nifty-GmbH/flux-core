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
        ->assertSet('workTimeTypeForm.id', $this->workTimeType->getKey())
        ->assertSet('workTimeTypeForm.name', 'Test Type')
        ->assertSet('workTimeTypeForm.is_billable', true);
});

test('inline edit resets form before filling', function (): void {
    $other = WorkTimeType::factory()->create(['name' => 'Other Type']);

    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', $this->workTimeType->getKey())
        ->assertSet('workTimeTypeForm.name', 'Test Type')
        ->call('inlineEdit', $other->getKey())
        ->assertSet('inlineEditingId', $other->getKey())
        ->assertSet('workTimeTypeForm.name', 'Other Type');
});

test('can save inline name change', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', $this->workTimeType->getKey())
        ->set('workTimeTypeForm.name', 'Updated Type')
        ->call('saveInline')
        ->assertReturned(true)
        ->assertHasNoErrors();

    $this->workTimeType->refresh();
    expect($this->workTimeType->name)->toBe('Updated Type');
});

test('can save inline billable toggle', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', $this->workTimeType->getKey())
        ->set('workTimeTypeForm.is_billable', false)
        ->call('saveInline')
        ->assertReturned(true)
        ->assertHasNoErrors();

    $this->workTimeType->refresh();
    expect($this->workTimeType->is_billable)->toBeFalse();
});

test('save inline with empty name fails validation', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', $this->workTimeType->getKey())
        ->set('workTimeTypeForm.name', '')
        ->call('saveInline')
        ->assertReturned(false);

    $this->workTimeType->refresh();
    expect($this->workTimeType->name)->toBe('Test Type');
});

test('can cancel inline edit without saving', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', $this->workTimeType->getKey())
        ->set('workTimeTypeForm.name', 'Changed Name')
        ->call('cancelInline')
        ->assertSet('inlineEditingId', null);

    $this->workTimeType->refresh();
    expect($this->workTimeType->name)->toBe('Test Type');
});

test('cancel resets form state', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', $this->workTimeType->getKey())
        ->assertSet('workTimeTypeForm.id', $this->workTimeType->getKey())
        ->call('cancelInline')
        ->assertSet('workTimeTypeForm.id', null)
        ->assertSet('workTimeTypeForm.name', null);
});

test('inline edit nonexistent row throws', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', 99999);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

test('inline editing id stays set after save without save button', function (): void {
    Livewire::test(WorkTimeTypes::class)
        ->call('inlineEdit', $this->workTimeType->getKey())
        ->set('workTimeTypeForm.name', 'New Name')
        ->call('saveInline')
        ->assertReturned(true)
        ->assertSet('inlineEditingId', $this->workTimeType->getKey());
});

test('get inline editable fields returns correct fields', function (): void {
    $form = new \FluxErp\Livewire\Forms\WorkTimeTypeForm(
        Livewire::test(WorkTimeTypes::class)->instance(),
        'workTimeTypeForm'
    );

    expect($form->getInlineEditableFields())
        ->toContain('name')
        ->toContain('is_billable')
        ->not->toContain('id');
});
