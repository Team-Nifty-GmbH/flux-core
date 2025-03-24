<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Unit;
use FluxErp\Rulesets\Unit\UpdateUnitRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateUnit extends FluxAction
{
    public static function models(): array
    {
        return [Unit::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateUnitRuleset::class;
    }

    public function performAction(): Model
    {
        $unit = resolve_static(Unit::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $unit->fill($this->data);
        $unit->save();

        return $unit->withoutRelations()->fresh();
    }
}
