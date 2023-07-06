<?php

namespace FluxErp\Actions\SerialNumberRange;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateSerialNumberRangeRequest;
use FluxErp\Models\SerialNumber;
use FluxErp\Models\SerialNumberRange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateSerialNumberRange implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateSerialNumberRangeRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'serial-number-range.update';
    }

    public static function description(): string|null
    {
        return 'update serial number range';
    }

    public static function models(): array
    {
        return [SerialNumberRange::class];
    }
    public function execute(): Model
    {
        $serialNumberRange = SerialNumberRange::query()
            ->whereKey($this->data['id'])
            ->first();

        $serialNumberRange->fill($this->data);
        $serialNumberRange->save();

        return $serialNumberRange->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        if ((array_key_exists('prefix', $this->data) || array_key_exists('affix', $this->data))
            && SerialNumber::query()
                ->where('serial_number_range_id', $this->data['id'])
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'serial_numbers' => [__('Serial number range has serial numbers')],
            ])->errorBag('updateSerialNumberRange');
        }

        return $this;
    }
}
