<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\AbsenceRequestStatusEnum;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\ChangeAbsenceRequestStatusRuleset;
use Illuminate\Support\Arr;

class RevokeAbsenceRequest extends FluxAction
{
    public static function models(): array
    {
        return [AbsenceRequest::class];
    }

    protected function getRulesets(): string|array
    {
        return ChangeAbsenceRequestStatusRuleset::class;
    }

    public function performAction(): AbsenceRequest
    {
        /** @var AbsenceRequest $absenceRequest */
        $absenceRequest = resolve_static(AbsenceRequest::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $data = $this->getData();
        $absenceRequest->statusChangeComment = Arr::pull($data, 'comment');

        $absenceRequest->fill(array_merge(
            $data,
            [
                'status' => AbsenceRequestStatusEnum::Revoked,
            ]
        ));
        $absenceRequest->save();

        return $absenceRequest->fresh();
    }
}
