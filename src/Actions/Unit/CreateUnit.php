<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Unit;
use FluxErp\Rulesets\Unit\CreateUnitRuleset;

class CreateUnit extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateUnitRuleset::class;
    }

    public static function models(): array
    {
        return [Unit::class];
    }

    public function performAction(): Unit
    {
        $unit = app(Unit::class, ['attributes' => $this->data]);
        $unit->save();

        return $unit->fresh();
    }
}
