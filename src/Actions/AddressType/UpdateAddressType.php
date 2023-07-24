<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateAddressTypeRequest;
use FluxErp\Models\AddressType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateAddressType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateAddressTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [AddressType::class];
    }

    public function execute(): Model
    {
        $addressType = AddressType::query()
            ->whereKey($this->data['id'])
            ->first();

        $addressType->fill($this->data);
        $addressType->save();

        return $addressType->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new AddressType());

        $this->data = $validator->validate();

        return $this;
    }
}
