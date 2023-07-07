<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateAddressTypeRequest;
use FluxErp\Models\AddressType;
use Illuminate\Support\Facades\Validator;

class CreateAddressType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateAddressTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [AddressType::class];
    }

    public function execute(): AddressType
    {
        $addressType = new AddressType($this->data);
        $addressType->save();

        return $addressType;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new AddressType());

        $this->data = $validator->validate();

        return $this;
    }
}
