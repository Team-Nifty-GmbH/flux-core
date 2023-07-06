<?php

namespace FluxErp\Actions\SerialNumber;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateSerialNumberRequest;
use FluxErp\Models\SerialNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateSerialNumber implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateSerialNumberRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'serial-number.update';
    }

    public static function description(): string|null
    {
        return 'update serial number';
    }

    public static function models(): array
    {
        return [SerialNumber::class];
    }

    public function execute(): Model
    {
        $serialNumber = SerialNumber::query()
            ->whereKey($this->data['id'])
            ->first();

        $serialNumber->fill($this->data);
        $serialNumber->save();

        return $serialNumber->withoutRelations()->fresh();
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

        $serialNumber = SerialNumber::query()
            ->whereKey($this->data['id'])
            ->first();

        $errors = [];
        if (
            ($this->data['product_id'] ?? false) &&
            $serialNumber->product_id &&
            $serialNumber->product_id !== $this->data['product_id']
        ) {
            $errors += ['product_id' => [__('Serial number already has a product_id')]];
        }

        if (
            ($this->data['order_position_id'] ?? false) &&
            $serialNumber->order_position_id &&
            $serialNumber->order_position_id !== $this->data['order_position_id']
        ) {
            $errors += ['order_position_id' => [__('Serial number already has an order_position_id')]];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updateSerialNumber');
        }

        return $this;
    }
}
