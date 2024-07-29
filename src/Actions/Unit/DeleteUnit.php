<?php

namespace FluxErp\Actions\Unit;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Unit;
use FluxErp\Rulesets\Unit\DeleteUnitRuleset;

class DeleteUnit extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteUnitRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Unit::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Unit::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
