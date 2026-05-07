<?php

namespace FluxErp\Rules;

use Closure;
use FluxErp\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductHierarchyDepth implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value)) {
            return;
        }

        $candidate = resolve_static(Product::class, 'query')
            ->whereKey($value)
            ->first();

        if (! $candidate) {
            return;
        }

        if (! is_null($candidate->parent_id)) {
            $fail('The :attribute may not point to a variant; only top-level products can have variants.')
                ->translate();
        }
    }
}
