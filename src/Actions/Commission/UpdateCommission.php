<?php

namespace FluxErp\Actions\Commission;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Commission;
use FluxErp\Rulesets\Commission\UpdateCommissionRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateCommission extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdateCommissionRuleset::class;
    }

    public static function models(): array
    {
        return [Commission::class];
    }

    public function performAction(): Model
    {
        $commission = resolve_static(Commission::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $commission->fill($this->data)
            ->save();

        return $commission->withoutRelations()->fresh();
    }
}
