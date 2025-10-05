<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Rulesets\AbsenceRequest\UpdateAbsenceRequestRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateAbsenceRequest extends FluxAction
{
    public static function models(): array
    {
        return [AbsenceRequest::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateAbsenceRequestRuleset::class;
    }

    public function performAction(): AbsenceRequest
    {
        $absenceRequest = resolve_static(AbsenceRequest::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $absenceRequest->fill($this->getData());
        $absenceRequest->save();

        return $absenceRequest->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        parent::validateData();

        $data = $this->getData();
        $substitutes = Arr::pull($data, 'substitutes');
        $absenceRequest = resolve_static(AbsenceRequest::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->fill($this->getData());

        if (is_array($substitutes)) {
            $absenceRequest->setRelation('substitutes', $substitutes);
        }

        $errors = [];
        $failedPolicies = $absenceRequest->failsAbsencePolicies();
        if ($failedPolicies && ! $absenceRequest->is_emergency) {
            $errors = array_merge($errors, $failedPolicies);
        }

        if ($absenceRequest->absenceType()
            ->where('affects_vacation', true)
            ->exists()
        ) {
            $employee = resolve_static(Employee::class, 'query')
                ->whereKey(data_get($data, 'employee_id'))
                ->first();
            if ($employee->getCurrentVacationDaysBalance() < $absenceRequest->calculateWorkDaysAffected()) {
                $errors += [
                    'vacation_days' => [__('Employee does not have enough vacation days available.')],
                ];
            }
        }

        if (
            $absenceRequest->isInBlackoutPeriod()
            && ! $absenceRequest->is_emergency
            && ! $absenceRequest->absenceType->affects_sick_leave
        ) {
            $errors += [
                'vacation_blackout' => [__('Absence request falls within a blackout period.')],
            ];
        }

        if (in_array($this->getData('employee_id'), $this->getData('substitutes') ?? [])) {
            $errors += [
                'substitute' => [__('Employee cannot be their own substitute.')],
            ];
        }

        $employee = $absenceRequest->employee;
        if ($employee?->employment_date?->greaterThan($absenceRequest->start_date)) {
            $errors += [
                'employment_date' => [__('Absence request starts before the employee\'s employment date.')],
            ];
        }

        if ($employee?->termination_date?->lessThan($absenceRequest->end_date)) {
            $errors += [
                'termination_date' => [__('Absence request ends after the employee\'s termination date.')],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
