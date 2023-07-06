<?php

namespace FluxErp\Actions\OrderPosition;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\OrderPosition;
use Illuminate\Support\Facades\Validator;

class DeleteOrderPosition implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:order_positions,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'order-position.delete';
    }

    public static function description(): string|null
    {
        return 'delete order position';
    }

    public static function models(): array
    {
        return [OrderPosition::class];
    }

    public function execute()
    {
        $orderPosition = OrderPosition::query()
            ->whereKey($this->data['id'])
            ->first();

        $orderPosition->children()->delete();

        return $orderPosition->delete();
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
