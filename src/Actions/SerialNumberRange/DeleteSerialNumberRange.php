<?php

namespace FluxErp\Actions\SerialNumberRange;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\SerialNumberRange;
use Illuminate\Support\Facades\Validator;

class DeleteSerialNumberRange implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:serial_number_ranges,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'serial-number-range.delete';
    }

    public static function description(): string|null
    {
        return 'delete serial number range';
    }

    public static function models(): array
    {
        return [SerialNumberRange::class];
    }

    public function execute()
    {
        return SerialNumberRange::query()
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
