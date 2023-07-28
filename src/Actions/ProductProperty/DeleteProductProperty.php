<?php

namespace FluxErp\Actions\ProductProperty;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\ProductProperty;
use Illuminate\Validation\ValidationException;

class DeleteProductProperty extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:product_properties,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [ProductProperty::class];
    }

    public function execute(): ?bool
    {
        return ProductProperty::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

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

        return $this;
    }
}
