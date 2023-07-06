<?php

namespace FluxErp\Actions\OrderType;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\OrderType;
use Illuminate\Support\Facades\Validator;

class DeleteOrderType implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:order_types,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'order-type.delete';
    }

    public static function description(): string|null
    {
        return 'delete order type';
    }

    public static function models(): array
    {
        return [OrderType::class];
    }

    public function execute(): bool|null
    {
        return OrderType::query()
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
