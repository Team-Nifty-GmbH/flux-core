<?php

namespace FluxErp\Actions\AbsenceRequest;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceRequest;
use FluxErp\Rulesets\AbsenceRequest\DeleteAbsenceRequestRuleset;

class DeleteAbsenceRequest extends FluxAction
{
    public static function models(): array
    {
        return [AbsenceRequest::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteAbsenceRequestRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(AbsenceRequest::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
