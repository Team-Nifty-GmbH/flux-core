<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Contact;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Support\Str;

class SupplierRuleset extends FluxRuleset
{
    public static function pivotFields(): array
    {
        return collect(array_keys(static::getRules()))
            ->filter(fn (string $key): bool => str_starts_with($key, 'suppliers.*.'))
            ->map(fn (string $key): string => Str::after($key, 'suppliers.*.'))
            ->reject(fn (string $field): bool => str_contains($field, '.'))
            ->unique()
            ->values()
            ->all();
    }

    public function rules(): array
    {
        return [
            'suppliers' => 'array',
            'suppliers.*.contact_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Contact::class]),
            ],
            'suppliers.*.manufacturer_product_number' => 'string|max:255|nullable',
            'suppliers.*.purchase_price' => 'numeric|nullable|min:0',
        ];
    }
}
