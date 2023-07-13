<?php

namespace FluxErp\Actions\VatRate;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateVatRateRequest;
use FluxErp\Models\VatRate;

class CreateVatRate extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateVatRateRequest())->rules();
    }

    public static function models(): array
    {
        return [VatRate::class];
    }

    public function execute(): VatRate
    {
        $vatRate = new VatRate($this->data);
        $vatRate->save();

        return $vatRate;
    }
}
