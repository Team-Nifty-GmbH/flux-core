<?php

namespace FluxErp\Rulesets\Product;

use Closure;
use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class PromoteParentToStandaloneRuleset extends FluxRuleset
{
    protected static ?string $model = Product::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class]),
                function (string $attribute, mixed $value, Closure $fail): void {
                    $product = resolve_static(Product::class, 'query')
                        ->whereKey($value)
                        ->first();

                    if (! $product) {
                        return;
                    }

                    if (! $product->was_parent) {
                        $fail(__('This product is not flagged as a former parent and does not need to be promoted to standalone.'));

                        return;
                    }

                    if ($product->children()->where('is_active', true)->exists()) {
                        $fail(__('Cannot promote: active variants still exist.'));
                    }
                },
            ],
        ];
    }
}
