<?php

use Carbon\Carbon;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Models\User;
use FluxErp\Notifications\AbsenceRequest\AbsenceRequestSubstituteAssignedNotification;

test('substitute assigned notification renders requesting employee name and dates', function (): void {
    $requestingEmployee = new Employee();
    $requestingEmployee->forceFill(['name' => 'Sonja Zitt']);

    $substitute = new Employee();
    $substitute->forceFill(['name' => 'Max']);

    $absenceRequest = new AbsenceRequest();
    $absenceRequest->forceFill([
        'id' => 1,
        'start_date' => Carbon::parse('2026-06-01'),
        'end_date' => Carbon::parse('2026-06-05'),
    ]);
    $absenceRequest->setRelation('employee', $requestingEmployee);

    $payload = (new AbsenceRequestSubstituteAssignedNotification($absenceRequest, $substitute))
        ->toArray(User::factory()->create());

    expect($payload['title'])->toBe(__('You are substitute for :employee', ['employee' => 'Sonja Zitt']))
        ->and($payload['description'])->toContain('2026-06-01')
        ->and($payload['description'])->toContain('2026-06-05');
});
