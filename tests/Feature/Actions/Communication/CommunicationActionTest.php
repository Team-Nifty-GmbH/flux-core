<?php

use FluxErp\Actions\Communication\CreateCommunication;
use FluxErp\Actions\Communication\DeleteCommunication;
use FluxErp\Actions\Communication\UpdateCommunication;
use FluxErp\Models\Communication;

test('create communication', function (): void {
    $comm = CreateCommunication::make([
        'communication_type_enum' => 'phone-call',
    ])->validate()->execute();

    expect($comm)->toBeInstanceOf(Communication::class);
});

test('update communication', function (): void {
    $comm = Communication::factory()->create();

    $updated = UpdateCommunication::make([
        'id' => $comm->getKey(),
        'subject' => 'Follow-up call',
    ])->validate()->execute();

    expect($updated->subject)->toBe('Follow-up call');
});

test('delete communication', function (): void {
    $comm = Communication::factory()->create();

    expect(DeleteCommunication::make(['id' => $comm->getKey()])
        ->validate()->execute())->toBeTrue();
});
