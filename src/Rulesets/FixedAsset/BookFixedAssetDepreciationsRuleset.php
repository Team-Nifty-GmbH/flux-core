<?php

namespace FluxErp\Rulesets\FixedAsset;

use FluxErp\Models\FixedAsset;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class BookFixedAssetDepreciationsRuleset extends FluxRuleset
{
    protected static ?string $model = FixedAsset::class;

    public function rules(): array
    {
        return [
            'fixed_asset_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => FixedAsset::class]),
            ],
            'until' => 'required|date',
        ];
    }
}
