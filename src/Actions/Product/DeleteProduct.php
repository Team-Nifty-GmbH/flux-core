<?php

namespace FluxErp\Actions\Product;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Product;
use Illuminate\Validation\ValidationException;

class DeleteProduct extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:products,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Product::class];
    }

    public function performAction(): ?bool
    {
        return Product::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        parent::validateData();

        if (Product::query()
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
