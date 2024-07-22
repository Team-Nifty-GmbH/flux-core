<?php

namespace FluxErp\Actions\SerialNumberRange;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Rulesets\SerialNumberRange\DeleteSerialNumberRangeRuleset;

class DeleteSerialNumberRange extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteSerialNumberRangeRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [SerialNumberRange::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(SerialNumberRange::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
