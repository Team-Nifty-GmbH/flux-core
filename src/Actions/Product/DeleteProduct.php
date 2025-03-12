<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Product;
use FluxErp\Rulesets\Product\DeleteProductRuleset;
use Illuminate\Validation\ValidationException;

class DeleteProduct extends FluxAction
{
    public static function models(): array
    {
        return [Product::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteProductRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Product::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Product::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->children()
            ->count() > 0
        ) {
            throw ValidationException::withMessages([
                'children' => [__('The given product has children')],
            ])->errorBag('deleteProduct');
        }
    }
}
