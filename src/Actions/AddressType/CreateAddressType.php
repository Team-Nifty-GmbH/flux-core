<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateAddressTypeRequest;
use FluxErp\Models\AddressType;
use Illuminate\Support\Facades\Validator;

class CreateAddressType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateAddressTypeRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'address-type.create';
    }

    public static function description(): string|null
    {
        return 'create address type';
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
