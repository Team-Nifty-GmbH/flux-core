<?php

namespace FluxErp\Actions\RecordOrigin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\RecordOrigin;
use FluxErp\Rulesets\RecordOrigin\DeleteRecordOriginRuleset;

class DeleteRecordOrigin extends FluxAction
{
    public static function models(): array
    {
        return [RecordOrigin::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteRecordOriginRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(RecordOrigin::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
