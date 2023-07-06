<?php

namespace FluxErp\Actions\DiscountGroup;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\DiscountGroup;
use Illuminate\Support\Facades\Validator;

class DeleteDiscountGroup implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:discount_groups,id',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'discount-group.delete';
    }

    public static function description(): string|null
    {
        return 'delete discount group';
    }

    public static function models(): array
    {
        return [DiscountGroup::class];
    }

    public function execute(): bool|null
    {
        return DiscountGroup::query()
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
