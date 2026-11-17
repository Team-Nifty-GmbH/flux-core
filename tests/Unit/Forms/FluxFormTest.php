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

test('filling a form from a model keeps values its toActionData derives', function (): void {
    $unit = Unit::factory()->create(['name' => 'Piece', 'abbreviation' => 'pc']);

    $form = new class(Livewire::test(Units::class)->instance(), 'unit') extends UnitForm
    {
        public string $abbreviationText = '';

        public function fill($values): void
        {
            parent::fill($values);

            $this->abbreviationText = (string) $this->abbreviation;
        }

        public function toActionData(): array
        {
            $this->abbreviation = trim($this->abbreviationText);

            $data = parent::toActionData();
            unset($data['abbreviationText']);

            return $data;
        }
    };

    $form->fill($unit);

    expect($form->abbreviationText)->toBe('pc');

    $form->name = 'Pieces';
    $form->setCheckPermission(false)->update();

    $unit->refresh();
    expect($unit->name)->toBe('Pieces')
        ->and($unit->abbreviation)->toBe('pc');
});
