<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\AddressType;
use Illuminate\Validation\ValidationException;

class DeleteAddressType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:address_types,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [AddressType::class];
    }

    public function execute(): ?bool
    {
        return AddressType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

        $errors = [];
        $addressType = AddressType::query()
            ->whereKey($this->data['id'])
            ->first();

        if ($addressType->is_lock) {
            $errors += [
                'is_locked' => [__('Address type is locked')],
            ];
        }

        if ($addressType->addresses()->exists()) {
            $errors += [
                'address' => [__('Address type has attached addresses')],
            ];
        }
        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('deleteAddressType');
        }

        return $this;
    }
}
