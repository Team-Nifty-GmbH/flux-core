<?php

namespace FluxErp\Actions\WorkTimeType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\WorkTimeType;
use FluxErp\Rulesets\WorkTimeType\UpdateWorkTimeTypeRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateWorkTimeType extends FluxAction
{
    public static function models(): array
    {
        return [WorkTimeType::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateWorkTimeTypeRuleset::class;
    }

    public function performAction(): Model
    {
        $workTimeType = resolve_static(WorkTimeType::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $workTimeType->fill($this->data);
        $workTimeType->save();

        return $workTimeType->withoutRelations()->fresh();
    }
}
