<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteProductOptionGroup implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:product_option_groups,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'product-option-group.delete';
    }

    public static function description(): string|null
    {
        return 'delete product option group';
    }

    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    public function execute(): bool|null
    {
        return ProductOptionGroup::query()
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

        if (ProductOptionGroup::query()
            ->whereKey($this->data['id'])
            ->first()
            ->productOptions()
            ->count() > 0
        ) {
            throw ValidationException::withMessages([
                'product_options' => [__('Product option group has product options')],
            ])->errorBag('deleteProductOptionGroup');
        }

        return $this;
    }
}
