<?php

namespace FluxErp\Actions\Address;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Address;
use Illuminate\Support\Facades\Validator;

class DeleteAddress implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:addresses,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'address.delete';
    }

    public static function description(): string|null
    {
        return 'delete address';
    }

    public static function models(): array
    {
        return [Address::class];
    }

    public function execute(): bool|null
    {
        $address = Address::query()
            ->whereKey($this->data['id'])
            ->first();

        $address->addressTypes()->detach();
        $address->tokens()->delete();

        return $address->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
