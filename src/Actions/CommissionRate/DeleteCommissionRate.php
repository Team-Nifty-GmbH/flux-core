<?php

namespace FluxErp\Actions\CommissionRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CommissionRate;
use FluxErp\Rulesets\CommissionRate\DeleteCommissionRateRuleset;

class DeleteCommissionRate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteCommissionRateRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [CommissionRate::class];
    }

    public function performAction(): ?bool
    {
        return app(CommissionRate::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
