<?php

use FluxErp\Livewire\Settings\Units;
use FluxErp\Models\Unit;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Units::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Units::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('unit.id', null)
        ->assertSet('unit.name', null)
        ->assertSet('unit.abbreviation', null)
        ->assertOpensModal('edit-unit-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $unit = Unit::factory()->create();

    Livewire::test(Units::class)
        ->call('edit', $unit->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('unit.id', $unit->getKey())
        ->assertSet('unit.name', $unit->name)
        ->assertSet('unit.abbreviation', $unit->abbreviation)
        ->assertOpensModal('edit-unit-modal');
});

test('can create via save', function (): void {
    Livewire::test(Units::class)
        ->call('edit')
        ->set('unit.name', 'Test Unit')
        ->set('unit.abbreviation', 'tu')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('units', [
        'name' => 'Test Unit',
        'abbreviation' => 'tu',
    ]);
});

test('can update via save', function (): void {
    $unit = Unit::factory()->create();

    Livewire::test(Units::class)
        ->call('edit', $unit->getKey())
        ->set('unit.name', 'Updated Unit')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('units', [
        'id' => $unit->getKey(),
        'name' => 'Updated Unit',
    ]);
});

test('can delete', function (): void {
    $unit = Unit::factory()->create();

    Livewire::test(Units::class)
        ->call('delete', $unit->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertSoftDeleted('units', ['id' => $unit->getKey()]);
});

test('save validates required fields', function (): void {
    Livewire::test(Units::class)
        ->call('edit')
        ->set('unit.name', null)
        ->call('save')
        ->assertReturned(false);
});
