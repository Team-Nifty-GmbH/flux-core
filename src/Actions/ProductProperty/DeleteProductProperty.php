<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductProperty;
use Illuminate\Validation\ValidationException;

class DeleteProductProperty extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:product_properties,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [ProductProperty::class];
    }

    public function performAction(): ?bool
    {
        return ProductProperty::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

        if (ProductProperty::query()
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
