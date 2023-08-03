<?php

namespace FluxErp\Actions\ProductOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductOption;

class DeleteProductOption extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:product_options,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [ProductOption::class];
    }

    public function performAction(): ?bool
    {
        return ProductOption::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
