<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateAddressTypeRequest;
use FluxErp\Models\AddressType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateAddressType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_silently(UpdateAddressTypeRequest::class)->rules();
    }

    public static function models(): array
    {
        return [AddressType::class];
    }

    public function performAction(): Model
    {
        $addressType = app(AddressType::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $addressType->fill($this->data);
        $addressType->save();

        return $addressType->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(AddressType::class));

        $this->data = $validator->validate();
    }
}
