<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Validation\ValidationException;

class DeleteProductOptionGroup extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:product_option_groups,id,deleted_at,NULL',
        ];
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

    public function validate(): static
    {
        parent::validate();

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
