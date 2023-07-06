<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateSerialNumberRequest;
use FluxErp\Models\SerialNumber;
use Illuminate\Support\Facades\Validator;

class CreateSerialNumber implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateSerialNumberRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'serial-number.create';
    }

    public static function description(): string|null
    {
        return 'create serial number';
    }

    public static function models(): array
    {
        return [SerialNumber::class];
    }

    public function execute(): SerialNumber
    {
        $serialNumber = new SerialNumber($this->data);
        $serialNumber->save();

        return $serialNumber;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new SerialNumber());

        $this->data = $validator->validate();

        return $this;
    }
}
