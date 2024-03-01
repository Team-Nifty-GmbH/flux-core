<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VatRate;
use FluxErp\Rulesets\VatRate\UpdateVatRateRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateVatRate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateVatRateRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function performAction(): Model
    {
        $vatRate = app(VatRate::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $vatRate->fill($this->data);
        $vatRate->save();

        return $vatRate->withoutRelations()->fresh();
    }
}
