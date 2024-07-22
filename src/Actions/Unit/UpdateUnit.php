<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Unit;
use FluxErp\Rulesets\Unit\UpdateUnitRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateUnit extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateUnitRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Unit::class];
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
