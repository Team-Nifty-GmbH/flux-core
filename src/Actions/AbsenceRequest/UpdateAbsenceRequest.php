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
use Illuminate\Support\Facades\Notification;
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
        $data = $this->getData();
        $substitutes = Arr::pull($data, 'substitutes');

        /** @var AbsenceRequest $absenceRequest */
        $absenceRequest = resolve_static(AbsenceRequest::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $absenceRequest->fill($data);
        $absenceRequest->save();

        $datesChanged = $absenceRequest->wasChanged(['start_date', 'end_date', 'start_time', 'end_time']);

        if (is_array($substitutes)) {
            $sync = $absenceRequest->substitutes()->sync($substitutes);

            $this->notifySubstituteIds(
                $absenceRequest,
                $sync['attached'],
                AbsenceRequestSubstituteAssignedNotification::class,
            );
            $this->notifySubstituteIds(
                $absenceRequest,
                $sync['detached'],
                AbsenceRequestSubstituteUnassignedNotification::class,
            );

            if ($datesChanged) {
                $kept = array_values(array_diff(
                    $absenceRequest->substitutes()->pluck('employees.id')->all(),
                    $sync['attached'],
                ));
                $this->notifySubstituteIds(
                    $absenceRequest,
                    $kept,
                    AbsenceRequestSubstituteAssignedNotification::class,
                );
            }
        } elseif ($datesChanged) {
            $this->notifySubstituteIds(
                $absenceRequest,
                $absenceRequest->substitutes()->pluck('employees.id')->all(),
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

    /**
     * @param  array<int, int>  $employeeIds
     * @param  class-string<\FluxErp\Notifications\Notification>  $notification
     */
    protected function notifySubstituteIds(
        AbsenceRequest $absenceRequest,
        array $employeeIds,
        string $notification,
    ): void {
        if (! $employeeIds) {
            return;
        }

        $authId = auth()->id();

        $users = resolve_static(Employee::class, 'query')
            ->whereIntegerInRaw('id', $employeeIds)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter(fn ($user) => $user && $user->getKey() !== $authId);

        if ($users->isNotEmpty()) {
            Notification::send($users, $notification::make($absenceRequest));
        }
    }
}
