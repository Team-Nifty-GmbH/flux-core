<?php

namespace FluxErp\Actions\AddressType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateAddressTypeRequest;
use FluxErp\Models\AddressType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateAddressType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateAddressTypeRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'address-type.update';
    }

    public static function description(): string|null
    {
        return 'update address type';
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
