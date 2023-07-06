<?php

namespace FluxErp\Actions\Discount;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Discount;
use Illuminate\Support\Facades\Validator;

class DeleteDiscount implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:discounts,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'discount.delete';
    }

    public static function description(): string|null
    {
        return 'delete discount';
    }

    public static function models(): array
    {
        return [Discount::class];
    }

    public function execute()
    {
        return Discount::query()
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
