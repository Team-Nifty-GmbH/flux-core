<?php

use FluxErp\Actions\LeadLossReason\CreateLeadLossReason;
use FluxErp\Actions\LeadLossReason\DeleteLeadLossReason;
use FluxErp\Actions\LeadLossReason\UpdateLeadLossReason;
use FluxErp\Models\LeadLossReason;

test('create lead loss reason', function (): void {
    $reason = CreateLeadLossReason::make(['name' => 'Zu teuer'])
        ->validate()->execute();

    expect($reason)->toBeInstanceOf(LeadLossReason::class)
        ->name->toBe('Zu teuer');
});

test('create lead loss reason requires name', function (): void {
    CreateLeadLossReason::assertValidationErrors([], 'name');
});

test('update lead loss reason', function (): void {
    $reason = LeadLossReason::factory()->create();

    $updated = UpdateLeadLossReason::make([
        'id' => $reason->getKey(),
        'name' => 'Kein Bedarf',
    ])->validate()->execute();

    expect($updated->name)->toBe('Kein Bedarf');
});

test('delete lead loss reason', function (): void {
    $reason = LeadLossReason::factory()->create();

    expect(DeleteLeadLossReason::make(['id' => $reason->getKey()])
        ->validate()->execute())->toBeTrue();
});
