<?php

namespace FluxErp\Rulesets\Product;

use FluxErp\Models\Product;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Database\Eloquent\Builder;

class PromoteParentToStandaloneRuleset extends FluxRuleset
{
    protected static ?string $model = Product::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Product::class])
                    ->where('is_variant_parent', true)
                    ->whereDoesntHave('children', fn (Builder $query) => $query->where('is_active', true)),
            ],
        ];
    }
}
