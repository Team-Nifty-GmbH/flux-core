<?php

use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Models\User;
use FluxErp\Notifications\AbsenceRequest\AbsenceRequestSubstituteUnassignedNotification;

test('substitute unassigned notification renders requesting employee name', function (): void {
    $requestingEmployee = new Employee();
    $requestingEmployee->forceFill(['name' => 'Sonja Zitt']);

    $substitute = new Employee();
    $substitute->forceFill(['name' => 'Max']);

    $absenceRequest = new AbsenceRequest();
    $absenceRequest->forceFill(['id' => 1]);
    $absenceRequest->setRelation('employee', $requestingEmployee);

    $payload = (new AbsenceRequestSubstituteUnassignedNotification($absenceRequest, $substitute))
        ->toArray(User::factory()->create());

    expect($payload['title'])->toBe(__('You are no longer substitute for :employee', ['employee' => 'Sonja Zitt']));
});
