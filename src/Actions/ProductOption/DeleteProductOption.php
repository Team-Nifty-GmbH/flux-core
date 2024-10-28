<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Product;
use FluxErp\Models\ProductOption;
use FluxErp\Rulesets\ProductOption\DeleteProductOptionRuleset;
use Illuminate\Validation\ValidationException;

class DeleteProductOption extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteProductOptionRuleset::class;
    }

    public static function models(): array
    {
        return [ProductOption::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(ProductOption::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Product::class, 'query')
            ->whereRelation('productOptions', 'product_option_id', $this->data['id'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'id' => [__('Product with given Product Option exists.')],
            ]);
        }
    }
}
