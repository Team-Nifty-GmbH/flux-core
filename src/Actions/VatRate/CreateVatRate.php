<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateVatRateRequest;
use FluxErp\Models\VatRate;

class CreateVatRate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateVatRateRequest())->rules();
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function performAction(): VatRate
    {
        $vatRate = new VatRate($this->data);
        $vatRate->save();

        return $vatRate->fresh();
    }
}
