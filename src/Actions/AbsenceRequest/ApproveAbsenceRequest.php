<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\ChangeAbsenceRequestStateRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ApproveAbsenceRequest extends FluxAction
{
    public static function models(): array
    {
        return [AbsenceRequest::class];
    }

    protected function getRulesets(): string|array
    {
        return ChangeAbsenceRequestStateRuleset::class;
    }

    public function performAction(): AbsenceRequest
    {
        /** @var AbsenceRequest $absenceRequest */
        $absenceRequest = resolve_static(AbsenceRequest::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $data = $this->getData();
        $absenceRequest->stateChangeComment = Arr::pull($data, 'comment');

        $absenceRequest->fill(array_merge(
            $data,
            [
                'state' => AbsenceRequestStateEnum::Approved,
            ]
        ));
        $absenceRequest->save();

        return $absenceRequest->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        /** @var AbsenceRequest $absenceRequest */
        $absenceRequest = resolve_static(AbsenceRequest::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        if ($absenceRequest->state !== AbsenceRequestStateEnum::Approved
            && $absenceRequest->intersections()
                ->where('state', AbsenceRequestStateEnum::Approved->value)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'id' => ['An approved absence request already exists in the given time period.'],
            ])
                ->errorBag('approveAbsenceRequest');
        }
    }
}
