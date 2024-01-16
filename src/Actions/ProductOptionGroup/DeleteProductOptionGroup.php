<?php

namespace FluxErp\Actions\ProductOptionGroup;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\ProductOption\DeleteProductOption;
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
        $productOptionGroup = ProductOptionGroup::query()
            ->whereKey($this->data['id'])
            ->first();

        $errors = false;
        foreach ($productOptionGroup->productOptions()->pluck('id')->toArray() as $productOption) {
            try {
                DeleteProductOption::make(['id' => $productOption])->validate()->execute();
            } catch (ValidationException) {
                $errors = true;
            }
        }

        return ! $errors ? $productOptionGroup->delete() : false;
    }
}
