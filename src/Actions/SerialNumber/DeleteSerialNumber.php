<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SerialNumber;
use FluxErp\Rulesets\SerialNumber\DeleteSerialNumberRuleset;

class DeleteSerialNumber extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteSerialNumberRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [SerialNumber::class];
    }

    public function performAction(): ?bool
    {
        return app(SerialNumber::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
