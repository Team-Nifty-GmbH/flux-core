<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Unit;
use FluxErp\Rulesets\Unit\CreateUnitRuleset;

class CreateUnit extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateUnitRuleset::class, 'getRules');
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
