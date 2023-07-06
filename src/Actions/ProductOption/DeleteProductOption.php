<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\ProductOption;
use Illuminate\Support\Facades\Validator;

class DeleteProductOption implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:product_options,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'product-option.delete';
    }

    public static function description(): string|null
    {
        return 'delete product option';
    }

    public static function models(): array
    {
        return [ProductOption::class];
    }

    public function execute()
    {
        return ProductOption::query()
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
