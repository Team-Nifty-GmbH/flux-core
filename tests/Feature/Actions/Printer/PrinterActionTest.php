<?php

use FluxErp\Actions\Printer\CreatePrinter;
use FluxErp\Actions\Printer\DeletePrinter;
use FluxErp\Actions\Printer\GeneratePrinterBridgeConfig;
use FluxErp\Actions\Printer\UpdatePrinter;
use Illuminate\Validation\ValidationException;

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

test('bridge config subscribes to mercure when the instance broadcasts over it', function (): void {
    config([
        'broadcasting.default' => 'mercure',
        'broadcasting.connections.mercure.public_url' => 'https://hub.example.com/.well-known/mercure',
        'broadcasting.connections.reverb.app_id' => 'reverb-id',
        'broadcasting.connections.reverb.secret' => 'reverb-secret',
    ]);

    $config = GeneratePrinterBridgeConfig::make(['instance_name' => 'mercure-bridge'])
        ->validate()
        ->execute();

    expect($config)
        ->mercure_enabled->toBeTrue()
        ->mercure_hub_url->toBe('https://hub.example.com/.well-known/mercure')
        ->reverb_disabled->toBeTrue()
        ->reverb_app_secret->toBe('');
});

test('bridge config leaves mercure off when the instance does not broadcast over it', function (): void {
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.app_id' => 'reverb-id',
        'broadcasting.connections.reverb.secret' => 'reverb-secret',
    ]);

    $config = GeneratePrinterBridgeConfig::make(['instance_name' => 'reverb-bridge'])
        ->validate()
        ->execute();

    expect($config)
        ->mercure_enabled->toBeFalse()
        ->mercure_hub_url->toBeNull()
        ->reverb_disabled->toBeFalse()
        ->reverb_app_secret->toBe('reverb-secret');
});

test('bridge config refuses mercure when the instance does not broadcast over it', function (): void {
    config(['broadcasting.default' => 'reverb']);

    GeneratePrinterBridgeConfig::make([
        'instance_name' => 'reverb-bridge',
        'mercure_enabled' => true,
    ])
        ->validate();
})
    ->throws(ValidationException::class, 'This instance does not broadcast over Mercure.');
