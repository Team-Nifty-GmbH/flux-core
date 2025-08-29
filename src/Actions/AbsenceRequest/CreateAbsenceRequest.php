<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\CreateAbsenceRequestRuleset;
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
        $absenceRequest = app(AbsenceRequest::class, ['attributes' => $this->data]);
        $absenceRequest->save();

        return $absenceRequest->fresh();
    }

    public function validateData(): void
    {
        parent::validateData();

        /** @var AbsenceRequest $absenceRequest */
        $absenceRequest = app(AbsenceRequest::class, ['attributes' => $this->data]);

        $errors = [];
        $failedPolicies = $absenceRequest->failsAbsencePolicies();
        if ($failedPolicies && ! $absenceRequest->is_emergency) {
            $errors = array_merge($errors, $failedPolicies);
        }

        if (
            $absenceRequest->isInBlackoutPeriod()
            && ! $absenceRequest->is_emergency
            && ! $absenceRequest->absenceType->affects_sick
        ) {
            $errors[] = __('Absence request falls within a blackout period.');
        }

        if ($errors) {
            throw ValidationException::withMessages([
                'absence_policy' => $errors,
            ]);
        }
    }
}
