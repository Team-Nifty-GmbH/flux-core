<?php

namespace FluxErp\Actions\RecordOrigin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\RecordOrigin;
use FluxErp\Rulesets\RecordOrigin\CreateRecordOriginRuleset;

class CreateRecordOrigin extends FluxAction
{
    public static function models(): array
    {
        return [RecordOrigin::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateRecordOriginRuleset::class;
    }

    public function performAction(): RecordOrigin
    {
        $recordOrigin = app(RecordOrigin::class, ['attributes' => $this->data]);
        $recordOrigin->save();

        return $recordOrigin->fresh();
    }
}
