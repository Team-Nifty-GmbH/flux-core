<?php

use FluxErp\Actions\Printer\CreatePrinter;
use FluxErp\Actions\PrinterUser\CreatePrinterUser;
use FluxErp\Actions\PrinterUser\DeletePrinterUser;

beforeEach(function (): void {
    $this->printer = CreatePrinter::make([
        'name' => 'Test Printer',
        'spooler_name' => 'test-spooler',
        'media_sizes' => ['A4'],
    ])->validate()->execute();
});

test('create printer user', function (): void {
    $pu = CreatePrinterUser::make([
        'printer_id' => $this->printer->getKey(),
        'user_id' => $this->user->getKey(),
    ])->validate()->execute();

    expect($pu)
        ->printer_id->toBe($this->printer->getKey())
        ->user_id->toBe($this->user->getKey());
});

test('create printer user requires printer_id and user_id', function (): void {
    CreatePrinterUser::assertValidationErrors([], ['printer_id', 'user_id']);
});

test('delete printer user', function (): void {
    $pu = CreatePrinterUser::make([
        'printer_id' => $this->printer->getKey(),
        'user_id' => $this->user->getKey(),
    ])->validate()->execute();

    expect(DeletePrinterUser::make(['pivot_id' => $pu->getKey()])
        ->validate()->execute())->toBeTrue();
});
