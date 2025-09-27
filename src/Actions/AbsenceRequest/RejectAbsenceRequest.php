<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\AbsenceRequestStateEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\ChangeAbsenceRequestStateRuleset;
use Illuminate\Support\Arr;

class RejectAbsenceRequest extends FluxAction
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
        $absenceRequest = resolve_static(AbsenceRequest::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $data = $this->getData();
        $absenceRequest->statusChangeComment = Arr::pull($data, 'comment');

        $absenceRequest->fill(array_merge(
            $data,
            [
                'status' => AbsenceRequestStateEnum::Rejected,
            ]
        ));
        $absenceRequest->save();

        return $absenceRequest->fresh();
    }
}
