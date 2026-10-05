<?php

namespace FluxErp\Rulesets\StorageArea;

use FluxErp\Models\StorageArea;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteStorageAreaRuleset extends FluxRuleset
{
    protected static ?string $model = StorageArea::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => StorageArea::class]),
            ],
        ];
    }
}
