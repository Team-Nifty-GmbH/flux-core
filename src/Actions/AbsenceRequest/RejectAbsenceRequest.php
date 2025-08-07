<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\RejectAbsenceRequestRuleset;
use Illuminate\Support\Facades\Auth;

class RejectAbsenceRequest extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return RejectAbsenceRequestRuleset::class;
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

        $absenceRequest->reject(
            Auth::user(),
            $this->data['rejection_reason']
        );

        return $absenceRequest->fresh();
    }
}