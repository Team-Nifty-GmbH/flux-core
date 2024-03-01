<?php

namespace FluxErp\Actions\Commission;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Commission;
use FluxErp\Rulesets\Commission\UpdateCommissionRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateCommission extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);

        $this->rules = resolve_static(UpdateCommissionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Commission::class];
    }

    public function performAction(): Model
    {
        $commission = app(Commission::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $commission->fill($this->data)
            ->save();

        return $commission->withoutRelations()->fresh();
    }
}
