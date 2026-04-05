<?php

use FluxErp\Actions\Contact\UpdateContact;
use FluxErp\Actions\WorkTime\CreateWorkTime;
use FluxErp\Actions\WorkTime\UpdateWorkTime;
use FluxErp\Models\Contact;

// --- VERIFIED BUG: UpdateContact null payment_type + tenant change ---

test('update contact tenant change with null payment_type does not fail', function (): void {
    $contact = Contact::factory()->create(['payment_type_id' => null]);

    $updated = UpdateContact::make([
        'id' => $contact->getKey(),
        'tenants' => [$this->dbTenant->getKey()],
    ])->validate()->execute();

    expect($updated)->not->toBeNull();
});

// --- VERIFIED BUG: UpdateWorkTime is_locked undefined key ---

test('update work time without is_locked does not crash', function (): void {
    $wt = CreateWorkTime::make([
        'user_id' => $this->user->getKey(),
        'name' => 'Test',
        'started_at' => '2026-04-05 09:00:00',
        'ended_at' => '2026-04-05 17:00:00',
    ])->validate()->execute();

    $updated = UpdateWorkTime::make([
        'id' => $wt->getKey(),
        'description' => 'Updated without is_locked',
    ])->validate()->execute();

    expect($updated->description)->toBe('Updated without is_locked');
});
