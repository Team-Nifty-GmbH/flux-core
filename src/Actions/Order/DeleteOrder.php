<?php

namespace FluxErp\Actions\Order;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteOrder implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:orders,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'order.delete';
    }

    public static function description(): string|null
    {
        return 'delete order';
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function execute()
    {
        return Order::query()
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

        $errors = [];
        $order = Order::query()
            ->whereKey($this->data['id'])
            ->first();

        if ($order->is_locked) {
            $errors += [
                'is_locked' => [__('Order is locked')]
            ];
        }

        if ($order->children()->count() > 0) {
            $errors += [
                'children' => [__('Order has children')]
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('deleteOrder');
        }

        return $this;
    }
}
