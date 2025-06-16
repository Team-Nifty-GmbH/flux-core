<?php

namespace FluxErp\Actions\RecordOrigin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\RecordOrigin;
use FluxErp\Rulesets\RecordOrigin\UpdateRecordOriginRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateRecordOrigin extends FluxAction
{
    public static function models(): array
    {
        return [RecordOrigin::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateRecordOriginRuleset::class;
    }

    public function performAction(): Model
    {
        $recordOrigin = resolve_static(RecordOrigin::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $recordOrigin->fill($this->data);
        $recordOrigin->save();

        return $recordOrigin->withoutRelations()->fresh();
    }
}
