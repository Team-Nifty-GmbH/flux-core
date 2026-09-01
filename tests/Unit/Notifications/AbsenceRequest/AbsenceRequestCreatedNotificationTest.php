<?php

use Carbon\Carbon;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Employee;
use FluxErp\Models\User;
use FluxErp\Notifications\AbsenceRequest\AbsenceRequestCreatedNotification;

test('title names the requesting employee', function (): void {
    $employee = new Employee();
    $employee->forceFill([
        'id' => 1,
        'name' => 'Sonja Zitt',
        'firstname' => 'Sonja',
        'lastname' => 'Zitt',
    ]);

    $absenceRequest = new AbsenceRequest();
    $absenceRequest->forceFill([
        'id' => 1,
        'start_date' => Carbon::parse('2026-06-01'),
        'end_date' => Carbon::parse('2026-06-05'),
    ]);
    $absenceRequest->setRelation('employee', $employee);

    $payload = (new AbsenceRequestCreatedNotification($absenceRequest))
        ->toArray(User::factory()->create());

    expect($payload)
        ->title->toBe(__(':employee filed a new absence request', ['employee' => 'Sonja Zitt']));
});

test('description includes absence type and period', function (): void {
    $absenceType = new AbsenceType();
    $absenceType->forceFill(['name' => 'Urlaub']);

    $absenceRequest = new AbsenceRequest();
    $absenceRequest->forceFill([
        'id' => 2,
        'start_date' => Carbon::parse('2026-06-01'),
        'end_date' => Carbon::parse('2026-06-05'),
    ]);
    $absenceRequest->setRelation('absenceType', $absenceType);
    $absenceRequest->setRelation('employee', null);

    $payload = (new AbsenceRequestCreatedNotification($absenceRequest))
        ->toArray(User::factory()->create());

    expect($payload['description'])->toContain('Urlaub')
        ->and($payload['description'])->toContain('2026-06-01')
        ->and($payload['description'])->toContain('2026-06-05');
});
