<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\UpdateAbsenceRequestRuleset;
use Illuminate\Database\Eloquent\Builder;
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
            ->whereKey($this->getData('id'))
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

        $employee = $absenceRequest->employee;
        if ($absenceType = $absenceRequest
            ->absenceType()
            ->where(
                fn (Builder $query) => $query
                    ->where('affects_sick_leave', true)
                    ->orWhere('affects_vacation', true)
            )
            ->first(['id', 'affects_vacation'])
        ) {
            if ($absenceRequest->day_part === AbsenceRequestDayPartEnum::Time) {
                $errors += [
                    'day_part' => ['Cannot select \'Time\' on given absence type.'],
                ];
            }

            if ($absenceType->affects_vacation
                && $employee->getVacationDaysBalance(
                    $absenceRequest->start_date
                ) < $absenceRequest->calculateWorkDaysAffected()
            ) {
                $errors += [
                    'vacation_days' => ['Not enough vacation days available.'],
                ];
            }
        }

        if (
            $absenceRequest->isInBlackoutPeriod()
            && ! $absenceRequest->is_emergency
            && ! $absenceRequest->absenceType->affects_sick_leave
        ) {
            $errors += [
                'vacation_blackout' => ['Absence request falls within a vacation blackout period.'],
            ];
        }

        if (in_array($this->getData('employee_id'), $this->getData('substitutes') ?? [])) {
            $errors += [
                'substitute' => ['Employee cannot be their own substitute.'],
            ];
        }

        if ($employee->employment_date?->greaterThan($absenceRequest->start_date)) {
            $errors += [
                'employment_date' => ['Absence request starts before the employee\'s employment date.'],
            ];
        }

        if ($employee->termination_date?->endOfDay()->lessThan($absenceRequest->end_date)) {
            $errors += [
                'termination_date' => ['Absence request ends after the employee\'s termination date.'],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
