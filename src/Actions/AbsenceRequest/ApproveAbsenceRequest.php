<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\ChangeAbsenceRequestStateRuleset;
use Illuminate\Support\Arr;

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
                'state_enum' => AbsenceRequestStateEnum::Approved,
            ]
        ));
        $absenceRequest->save();

        return $absenceRequest->fresh();
    }
}
