<?php

namespace FluxErp\Rules;

use Closure;
use FluxErp\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductIsOrderable implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value)) {
            return;
        }

        $product = resolve_static(Product::class, 'query')
            ->whereKey($value)
            ->first();

        if (! $product) {
            return;
        }

        $hasActiveChildren = $product->children()->where('is_active', true)->exists();

        if ($hasActiveChildren || $product->was_parent) {
            $fail('The selected product has variants — please select a variant instead.')
                ->translate();
        }
    }
}
