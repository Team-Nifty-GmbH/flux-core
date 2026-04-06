<?php

use FluxErp\Actions\TicketType\CreateTicketType;
use FluxErp\Actions\TicketType\DeleteTicketType;
use FluxErp\Actions\TicketType\UpdateTicketType;
use FluxErp\Models\TicketType;

test('create ticket type', function (): void {
    $type = CreateTicketType::make(['name' => 'Bug Report'])
        ->validate()->execute();

    expect($type)->toBeInstanceOf(TicketType::class)
        ->name->toBe('Bug Report');
});

test('create ticket type requires name', function (): void {
    CreateTicketType::assertValidationErrors([], 'name');
});

test('update ticket type', function (): void {
    $type = TicketType::factory()->create();

    $updated = UpdateTicketType::make([
        'id' => $type->getKey(),
        'name' => 'Feature Request',
    ])->validate()->execute();

    expect($updated->name)->toBe('Feature Request');
});

test('delete ticket type', function (): void {
    $type = TicketType::factory()->create();

    expect(DeleteTicketType::make(['id' => $type->getKey()])
        ->validate()->execute())->toBeTrue();
});
