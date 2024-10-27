<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductProperty;
use FluxErp\Rulesets\ProductProperty\DeleteProductPropertyRuleset;
use Illuminate\Validation\ValidationException;

class DeleteProductProperty extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteProductPropertyRuleset::class;
    }

    public static function models(): array
    {
        return [ProductProperty::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(ProductProperty::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(ProductProperty::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->products()
            ->count() > 0
        ) {
            throw ValidationException::withMessages([
                'products' => [__('Product property has products')],
            ])->errorBag('deleteProductProperty');
        }
    }
}
