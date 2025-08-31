<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\UpdateAbsenceRequestRuleset;
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

        return $absenceRequest->fresh();
    }

    public function validateData(): void
    {
        parent::validateData();

        $absenceRequest = resolve_static(AbsenceRequest::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->fill($this->getData());

        $errors = [];
        $failedPolicies = $absenceRequest->failsAbsencePolicies();
        if ($failedPolicies && ! $absenceRequest->is_emergency) {
            $errors = array_merge($errors, $failedPolicies);
        }

        if ($absenceRequest->isInBlackoutPeriod() && ! $absenceRequest->is_emergency) {
            $errors[] = __('Absence request falls within a blackout period.');
        }

        if ($errors) {
            throw ValidationException::withMessages([
                'absence_policy' => $errors,
            ]);
        }
    }
}
