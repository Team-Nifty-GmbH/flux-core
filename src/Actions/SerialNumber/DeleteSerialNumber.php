<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\SerialNumber;
use Illuminate\Support\Facades\Validator;

class DeleteSerialNumber implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:serial_numbers,id',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'serial-number.delete';
    }

    public static function description(): string|null
    {
        return 'delete serial number';
    }

    public static function models(): array
    {
        return [SerialNumber::class];
    }

    public function execute()
    {
        return SerialNumber::query()
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

        return $this;
    }
}
