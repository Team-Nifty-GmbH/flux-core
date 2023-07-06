<?php

namespace FluxErp\Actions\Price;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Price;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeletePrice implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:prices,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'price.delete';
    }

    public static function description(): string|null
    {
        return 'delete price';
    }

    public static function models(): array
    {
        return [Price::class];
    }

    public function execute(): bool|null
    {
        return Price::query()
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

        if (Price::query()
            ->whereKey($this->data['id'])
            ->first()
            ->orderPositions()
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'order_positions' => [__('Price has associated order positions')],
            ])->errorBag('deletePrice');
        }

        return $this;
    }
}
