<?php

namespace FluxErp\Actions\AbsenceRequest;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\CreateAbsenceRequestRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreateAbsenceRequest extends FluxAction
{
    public static function models(): array
    {
        return [AbsenceRequest::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateAbsenceRequestRuleset::class;
    }

    public function performAction(): AbsenceRequest
    {
        $data = $this->getData();
        $substitutes = Arr::pull($data, 'substitutes');

        $absenceRequest = app(AbsenceRequest::class, ['attributes' => $data]);
        $absenceRequest->save();

        if ($substitutes) {
            $absenceRequest->substitutes()->attach($substitutes);
        }

        return $absenceRequest->refresh();
    }

    public function validateData(): void
    {
        parent::validateData();

        /** @var AbsenceRequest $absenceRequest */
        $data = $this->getData();
        $substitutes = Arr::pull($data, 'substitutes');

        $absenceRequest = app(AbsenceRequest::class, ['attributes' => $data]);
        $absenceRequest->setRelation('substitutes', $substitutes);

        $errors = [];
        $failedPolicies = $absenceRequest->failsAbsencePolicies();
        if ($failedPolicies && ! $absenceRequest->is_emergency) {
            $errors = array_merge($errors, $failedPolicies);
        }

        $employee = $absenceRequest->employee;
        $absenceType = $absenceRequest
            ->absenceType()
            ->first([
                'id',
                'affects_overtime',
                'affects_sick_leave',
                'affects_vacation',
            ]);

        if ($absenceType->affects_sick_leave || $absenceType->affects_vacation) {
            if ($this->getData('day_part') === AbsenceRequestDayPartEnum::Time) {
                $errors += [
                    'day_part' => ['Cannot select \'Time\' on given absence type.'],
                ];
            }

            if ($absenceType->affects_vacation
                && $employee->getVacationDaysBalance(
                    Carbon::parse($this->getData('start_date'))
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

        if ($absenceRequest
            ->intersections([AbsenceRequestStateEnum::Rejected, AbsenceRequestStateEnum::Revoked])
            ->whereHas(
                'absenceType',
                fn (Builder $query) => $query
                    ->when(
                        $absenceType->affects_sick_leave,
                        fn (Builder $query) => $query
                            ->where('affects_sick_leave', true)
                    )
                    ->when(
                        $absenceType->affects_vacation,
                        fn (Builder $query) => $query
                            ->where('affects_sick_leave', true)
                            ->orWhere('affects_vacation', true)
                    )
                    ->when(
                        $absenceType->affects_overtime,
                        fn (Builder $query) => $query
                            ->where('affects_sick_leave', true)
                            ->orWhere('affects_vacation', true)
                            ->orWhere('affects_overtime', true)
                    )
            )
            ->exists()
        ) {
            $errors += [
                'overlap' => ['Absence request overlaps with an existing approved or pending absence request.'],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
