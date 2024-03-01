<?php

namespace FluxErp\Actions\Commission;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Commission;
use FluxErp\Rulesets\Commission\DeleteCommissionRuleset;

class DeleteCommission extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteCommissionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Commission::class];
    }

    public function performAction(): ?bool
    {
        return app(Commission::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
