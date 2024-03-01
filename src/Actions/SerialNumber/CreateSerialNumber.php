<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SerialNumber;
use FluxErp\Rulesets\SerialNumber\CreateSerialNumberRuleset;
use Illuminate\Support\Facades\Validator;

class CreateSerialNumber extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateSerialNumberRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [SerialNumber::class];
    }

    public function performAction(): SerialNumber
    {
        $serialNumber = app(SerialNumber::class, ['attributes' => $this->data]);
        $serialNumber->save();

        return $serialNumber->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(SerialNumber::class));

        $this->data = $validator->validate();
    }
}
