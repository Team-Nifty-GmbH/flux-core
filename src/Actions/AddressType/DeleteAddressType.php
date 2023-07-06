<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\AddressType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteAddressType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:address_types,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'address-type.delete';
    }

    public static function description(): string|null
    {
        return 'delete address type';
    }

    public static function models(): array
    {
        return [AddressType::class];
    }

    public function execute()
    {
        return AddressType::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        $errors = [];
        $addressType = AddressType::query()
            ->whereKey($this->data['id'])
            ->first();

        if ($addressType->is_lock) {
            $errors += [
                'is_locked' => [__('Address type is locked')]
            ];
        }

        if ($addressType->addresses()->exists()) {
            $errors += [
                'address' => [__('Address type has attached addresses')]
            ];
        }
        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('deleteAddressType');
        }

        return $this;
    }
}
