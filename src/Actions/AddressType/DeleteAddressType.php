<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AddressType;
use FluxErp\Rulesets\AddressType\DeleteAddressTypeRuleset;
use Illuminate\Validation\ValidationException;

class DeleteAddressType extends FluxAction
{
    public static function models(): array
    {
        return [AddressType::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteAddressTypeRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(AddressType::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $addressType = resolve_static(AddressType::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if ($addressType->is_lock) {
            $errors += [
                'is_locked' => ['Address type is locked'],
            ];
        }

        if ($addressType->addresses()->exists()) {
            $errors += [
                'address' => ['Address type has attached addresses'],
            ];
        }
        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('deleteAddressType');
        }
    }
}
