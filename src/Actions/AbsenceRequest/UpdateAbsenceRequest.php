<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\UpdateAbsenceRequestRuleset;

class UpdateAbsenceRequest extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateAbsenceRequestRuleset::class;
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

        $absenceRequest->fill($this->data);
        $absenceRequest->save();

        return $absenceRequest->fresh();
    }
}