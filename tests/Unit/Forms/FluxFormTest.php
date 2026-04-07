<?php

use FluxErp\Livewire\Forms\UnitForm;
use FluxErp\Livewire\Settings\Units;
use FluxErp\Models\Unit;
use Livewire\Livewire;

test('form save creates new record', function (): void {
    Livewire::test(Units::class)
        ->set('unit.name', 'Kilogramm')
        ->set('unit.abbreviation', 'kg')
        ->call('save');

    expect(Unit::query()->where('name', 'Kilogramm')->exists())->toBeTrue();
});

test('form toActionData returns public properties', function (): void {
    $form = new UnitForm(Livewire::test(Units::class)->instance(), 'unit');
    $form->name = 'Test';
    $form->abbreviation = 'T';

    $data = $form->toActionData();

    expect($data)->toHaveKey('name', 'Test')
        ->toHaveKey('abbreviation', 'T');
});

test('form canAction checks permission', function (): void {
    $form = new UnitForm(Livewire::test(Units::class)->instance(), 'unit');

    expect($form->canAction('create'))->toBeBool();
    expect($form->canAction('nonexistent'))->toBeFalse();
});
