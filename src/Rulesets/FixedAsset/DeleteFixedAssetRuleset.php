<?php

namespace FluxErp\Rulesets\FixedAsset;

use FluxErp\Models\FixedAsset;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteFixedAssetRuleset extends FluxRuleset
{
    protected static ?string $model = FixedAsset::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => FixedAsset::class]),
            ],
        ];
    }
}
