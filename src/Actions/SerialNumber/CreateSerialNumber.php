<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SerialNumber;
use FluxErp\Rulesets\SerialNumber\CreateSerialNumberRuleset;
use Illuminate\Support\Arr;

class CreateSerialNumber extends FluxAction
{
    public static function models(): array
    {
        return [SerialNumber::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateSerialNumberRuleset::class;
    }

    public function performAction(): SerialNumber
    {
        $data = $this->data;
        Arr::forget($data, 'use_supplier_serial_number');

        $serialNumber = app(SerialNumber::class, ['attributes' => $data]);
        $serialNumber->save();

        return $serialNumber->fresh();
    }

    protected function prepareForValidation(): void
    {
        if (data_get($this->data, 'use_supplier_serial_number') === true) {
            $this->data['serial_number'] = data_get($this->data, 'supplier_serial_number');
        }
    }
}
