<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\CreateAbsenceRequestRuleset;

class CreateAbsenceRequest extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateAbsenceRequestRuleset::class;
    }

    public static function models(): array
    {
        return [AbsenceRequest::class];
    }

    public function performAction(): AbsenceRequest
    {
        $absenceRequest = app(AbsenceRequest::class, ['attributes' => $this->data]);
        $absenceRequest->save();

        return $absenceRequest->fresh();
    }
}