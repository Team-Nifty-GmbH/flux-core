<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;

class DeleteProduct extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:products,id,deleted_at,NULL',
        ];
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

    public function validate(): static
    {
        parent::validate();

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
