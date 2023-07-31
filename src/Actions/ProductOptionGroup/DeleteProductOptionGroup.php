<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductOptionGroup;
use Illuminate\Validation\ValidationException;

class DeleteProductOptionGroup extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:product_option_groups,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [ProductOptionGroup::class];
    }

    public function performAction(): ?bool
    {
        return ProductOptionGroup::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

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
    }
}
