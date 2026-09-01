<?php

use FluxErp\Livewire\Settings\Printers;
use FluxErp\Models\Printer;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Printers::class)
        ->assertOk();
});

test('edit with model fills form and opens modal', function (): void {
    $printer = Printer::query()->create([
        'name' => 'Office Printer',
        'spooler_name' => 'hp-laserjet',
        'media_sizes' => ['A4', 'A3'],
        'is_active' => true,
    ]);

    Livewire::test(Printers::class)
        ->call('edit', $printer->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('printerForm.id', $printer->getKey())
        ->assertSet('printerForm.alias', $printer->alias)
        ->assertSet('printerForm.is_visible', $printer->is_visible)
        ->assertOpensModal('edit-printer-modal');
});

test('can update printer alias', function (): void {
    $printer = Printer::query()->create([
        'name' => 'Office Printer',
        'spooler_name' => 'hp-laserjet',
        'media_sizes' => ['A4'],
        'is_active' => true,
    ]);

    Livewire::test(Printers::class)
        ->call('edit', $printer->getKey())
        ->assertSet('printerForm.id', $printer->getKey())
        ->set('printerForm.alias', 'My Printer Alias')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($printer->refresh()->alias)->toEqual('My Printer Alias');
});

test('can update printer visibility', function (): void {
    $printer = Printer::query()->create([
        'name' => 'Office Printer',
        'spooler_name' => 'hp-laserjet',
        'media_sizes' => ['A4'],
        'is_active' => true,
        'is_visible' => false,
    ]);

    Livewire::test(Printers::class)
        ->call('edit', $printer->getKey())
        ->assertSet('printerForm.is_visible', false)
        ->set('printerForm.is_visible', true)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($printer->refresh()->is_visible)->toBeTrue();
});

test('can delete spooler and associated printers', function (): void {
    $spoolerName = 'test-spooler-delete';

    $printer1 = Printer::query()->create([
        'name' => 'Printer 1',
        'spooler_name' => $spoolerName,
        'media_sizes' => ['A4'],
        'is_active' => true,
    ]);

    $printer2 = Printer::query()->create([
        'name' => 'Printer 2',
        'spooler_name' => $spoolerName,
        'media_sizes' => ['A4'],
        'is_active' => true,
    ]);

    Livewire::test(Printers::class)
        ->set('deleteSpoolerName', $spoolerName)
        ->call('deleteSpooler')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('printers', ['id' => $printer1->getKey()]);
    $this->assertDatabaseMissing('printers', ['id' => $printer2->getKey()]);
});

test('delete spooler fails without spooler name', function (): void {
    $result = Livewire::test(Printers::class)
        ->call('deleteSpooler');

    $result->assertOk();
});

test('edit resets form between different printers', function (): void {
    $printer1 = Printer::query()->create([
        'name' => 'Printer One',
        'spooler_name' => 'spooler-1',
        'media_sizes' => ['A4'],
        'alias' => 'First Alias',
        'is_active' => true,
    ]);

    $printer2 = Printer::query()->create([
        'name' => 'Printer Two',
        'spooler_name' => 'spooler-2',
        'media_sizes' => ['A3'],
        'alias' => 'Second Alias',
        'is_active' => true,
    ]);

    Livewire::test(Printers::class)
        ->call('edit', $printer1->getKey())
        ->assertSet('printerForm.id', $printer1->getKey())
        ->assertSet('printerForm.alias', 'First Alias')
        ->call('edit', $printer2->getKey())
        ->assertSet('printerForm.id', $printer2->getKey())
        ->assertSet('printerForm.alias', 'Second Alias');
});
