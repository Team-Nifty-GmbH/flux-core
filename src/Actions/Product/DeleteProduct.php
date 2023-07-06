<?php

namespace FluxErp\Actions\Product;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteProduct implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:products,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'product.delete';
    }

    public static function description(): string|null
    {
        return 'delete product';
    }

    public static function models(): array
    {
        return [Product::class];
    }

    public function execute(): bool|null
    {
        return Product::query()
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

        if (Product::query()
            ->whereKey($this->data['id'])
            ->first()
            ->children()
            ->count() > 0
        ) {
            throw ValidationException::withMessages([
                'children' => [__('The given product has children')],
            ])->errorBag('deleteProduct');
        }

        return $this;
    }
}
