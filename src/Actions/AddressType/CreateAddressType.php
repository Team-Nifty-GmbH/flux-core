<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateAddressTypeRequest;
use FluxErp\Models\AddressType;
use Illuminate\Support\Facades\Validator;

class CreateAddressType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_silently(CreateAddressTypeRequest::class)->rules();
    }

    public static function models(): array
    {
        return [AddressType::class];
    }

    public function performAction(): AddressType
    {
        $addressType = app(AddressType::class, ['attributes' => $this->data]);
        $addressType->save();

        return $addressType->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(AddressType::class));

        $this->data = $validator->validate();
    }
}
