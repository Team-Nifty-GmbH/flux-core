<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\AbsenceRequestDayPartEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Models\Employee;
use FluxErp\Notifications\AbsenceRequest\AbsenceRequestSubstituteAssignedNotification;
use FluxErp\Notifications\AbsenceRequest\AbsenceRequestSubstituteUnassignedNotification;
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
        /** @var AbsenceRequest $absenceRequest */
        $absenceRequest = resolve_static(AbsenceRequest::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $oldSubstituteIds = $absenceRequest->substitutes()->pluck('employees.id')->all();
        $datesChanged = $this->datesChanged($absenceRequest);

        $absenceRequest->fill(Arr::except($this->getData(), 'substitutes'));
        $absenceRequest->save();

        $substitutesInInput = array_key_exists('substitutes', $this->data);
        if ($substitutesInInput) {
            $absenceRequest->substitutes()->sync($this->getData('substitutes') ?? []);
        }

        $newSubstituteIds = $absenceRequest->substitutes()->pluck('employees.id')->all();
        $added = array_values(array_diff($newSubstituteIds, $oldSubstituteIds));
        $removed = array_values(array_diff($oldSubstituteIds, $newSubstituteIds));
        $kept = array_values(array_intersect($newSubstituteIds, $oldSubstituteIds));

        if ($substitutesInInput) {
            $this->notifySubstitutes(
                $absenceRequest,
                $added,
                AbsenceRequestSubstituteAssignedNotification::class,
            );
            $this->notifySubstitutes(
                $absenceRequest,
                $removed,
                AbsenceRequestSubstituteUnassignedNotification::class,
            );
        }

        if ($datesChanged) {
            $this->notifySubstitutes(
                $absenceRequest,
                $kept,
                AbsenceRequestSubstituteAssignedNotification::class,
            );
        }

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
            $absenceRequest->setRelation(
                'substitutes',
                resolve_static(Employee::class, 'query')->whereKey($substitutes)->get()
            );
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
            if ($absenceRequest->day_part?->value === AbsenceRequestDayPartEnum::Time) {
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

    protected function datesChanged(AbsenceRequest $absenceRequest): bool
    {
        foreach (['start_date', 'end_date'] as $field) {
            if (! array_key_exists($field, $this->data)) {
                continue;
            }
            if ($absenceRequest->{$field}?->toDateString() !== $this->getData($field)) {
                return true;
            }
        }
        foreach (['start_time', 'end_time'] as $field) {
            if (! array_key_exists($field, $this->data)) {
                continue;
            }
            if ((string) $absenceRequest->{$field} !== (string) $this->getData($field)) {
                return true;
            }
        }

        return false;
    }

    protected function notifySubstitutes(
        AbsenceRequest $absenceRequest,
        array $employeeIds,
        string $notification,
    ): void {
        if (! $employeeIds) {
            return;
        }

        $authId = auth()->id();

        resolve_static(Employee::class, 'query')
            ->whereIntegerInRaw('id', $employeeIds)
            ->with('user')
            ->get()
            ->each(function (Employee $employee) use ($absenceRequest, $notification, $authId): void {
                $user = $employee->user;
                if ($user && $user->getKey() !== $authId) {
                    $user->notify(new $notification($absenceRequest, $employee));
                }
            });
    }
}
