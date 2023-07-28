<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\AddressType;
use Illuminate\Validation\ValidationException;

class DeleteAddressType extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:address_types,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [AddressType::class];
    }

    public function performAction(): ?bool
    {
        return AddressType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

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
    }
}
