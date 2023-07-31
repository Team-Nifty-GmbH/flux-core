<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateSerialNumberRequest;
use FluxErp\Models\SerialNumber;
use Illuminate\Support\Facades\Validator;

class CreateSerialNumber extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateSerialNumberRequest())->rules();
    }

    public static function models(): array
    {
        return [SerialNumber::class];
    }

    public function performAction(): SerialNumber
    {
        $serialNumber = new SerialNumber($this->data);
        $serialNumber->save();

        return $serialNumber;
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new SerialNumber());

        $this->data = $validator->validate();
    }
}
