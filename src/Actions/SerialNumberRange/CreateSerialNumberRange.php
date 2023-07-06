<?php

namespace FluxErp\Actions\SerialNumberRange;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateSerialNumberRangeRequest;
use FluxErp\Models\SerialNumberRange;
use Illuminate\Support\Facades\Validator;

class CreateSerialNumberRange implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateSerialNumberRangeRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'serial-number-range.create';
    }

    public static function description(): string|null
    {
        return 'create serial number range';
    }

    public static function models(): array
    {
        return [SerialNumberRange::class];
    }

    public function execute(): SerialNumberRange
    {
        $this->data['current_number'] = array_key_exists('start_number', $this->data) ?
            --$this->data['start_number'] : 0;
        unset($this->data['start_number']);

        $serialNumberRange = new SerialNumberRange($this->data);
        $serialNumberRange->save();

        return $serialNumberRange;
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
