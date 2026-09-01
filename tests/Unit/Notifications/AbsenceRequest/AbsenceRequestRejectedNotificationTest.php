<?php

use Carbon\Carbon;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\User;
use FluxErp\Notifications\AbsenceRequest\AbsenceRequestRejectedNotification;

test('rejected notification renders title and absence type', function (): void {
    $absenceType = new AbsenceType();
    $absenceType->forceFill(['name' => 'Urlaub']);

    $absenceRequest = new AbsenceRequest();
    $absenceRequest->forceFill([
        'id' => 1,
        'start_date' => Carbon::parse('2026-06-01'),
        'end_date' => Carbon::parse('2026-06-05'),
    ]);
    $absenceRequest->setRelation('absenceType', $absenceType);

    $payload = (new AbsenceRequestRejectedNotification($absenceRequest))
        ->toArray(User::factory()->create());

    expect($payload['title'])->toBe(__('Your absence request was rejected'))
        ->and($payload['description'])->toContain('Urlaub')
        ->and($payload['description'])->toContain('2026-06-01');
});
