<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\VatRate\DeleteVatRateRuleset;

class DeleteVatRate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteVatRateRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function performAction(): ?bool
    {
        return app(VatRate::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
