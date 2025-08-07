<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\ApproveAbsenceRequestRuleset;
use Illuminate\Support\Facades\Auth;

class ApproveAbsenceRequest extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return ApproveAbsenceRequestRuleset::class;
    }

    public static function models(): array
    {
        return [AbsenceRequest::class];
    }

    public function performAction(): AbsenceRequest
    {
        $absenceRequest = resolve_static(AbsenceRequest::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $absenceRequest->approve(
            Auth::user(),
            data_get($this->data, 'approval_note')
        );

        return $absenceRequest->fresh();
    }
}