<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateSerialNumberRequest;
use FluxErp\Models\SerialNumber;
use Illuminate\Support\Facades\Validator;

class CreateSerialNumber extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateSerialNumberRequest())->rules();
    }

    public static function models(): array
    {
        return [SerialNumber::class];
    }

    public function execute(): SerialNumber
    {
        $serialNumber = new SerialNumber($this->data);
        $serialNumber->save();

        return $serialNumber;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new SerialNumber());

        $this->data = $validator->validate();

        return $this;
    }
}
