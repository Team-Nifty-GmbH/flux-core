<?php

namespace FluxErp\Actions\ProductCrossSelling;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ProductCrossSelling;

class DeleteProductCrossSelling extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:product_cross_sellings,id',
        ];
    }

    public static function models(): array
    {
        return [ProductCrossSelling::class];
    }

    public function performAction(): bool
    {
        return ProductCrossSelling::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
