<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AddressType;
use FluxErp\Rules\ModelExists;
use Illuminate\Validation\ValidationException;

class DeleteAddressType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => [
                'required',
                'integer',
                new ModelExists(AddressType::class),
            ],
        ];
    }

    public static function models(): array
    {
        return [AddressType::class];
    }

    public function performAction(): ?bool
    {
        return app(AddressType::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $addressType = app(AddressType::class)->query()
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
