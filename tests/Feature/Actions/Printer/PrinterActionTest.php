<?php

use FluxErp\Actions\Printer\CreatePrinter;
use FluxErp\Actions\Printer\DeletePrinter;
use FluxErp\Actions\Printer\UpdatePrinter;

test('create printer', function (): void {
    $printer = CreatePrinter::make([
        'name' => 'Office Printer',
        'spooler_name' => 'hp-laserjet',
        'media_sizes' => ['A4', 'A3'],
    ])->validate()->execute();

    expect($printer)->name->toBe('Office Printer');
});

test('create printer requires name spooler_name media_sizes', function (): void {
    CreatePrinter::assertValidationErrors([], ['name', 'spooler_name', 'media_sizes']);
});

test('update printer', function (): void {
    $printer = CreatePrinter::make([
        'name' => 'Original',
        'spooler_name' => 'old-spooler',
        'media_sizes' => ['A4'],
    ])->validate()->execute();

    $updated = UpdatePrinter::make([
        'id' => $printer->getKey(),
        'name' => 'New Printer',
    ])->validate()->execute();

    expect($updated->name)->toBe('New Printer');
});

test('delete printer', function (): void {
    $printer = CreatePrinter::make([
        'name' => 'Temp',
        'spooler_name' => 'temp-spooler',
        'media_sizes' => ['A4'],
    ])->validate()->execute();

    expect(DeletePrinter::make(['id' => $printer->getKey()])
        ->validate()->execute())->toBeTrue();
});
