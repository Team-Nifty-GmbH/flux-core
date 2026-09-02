<?php

namespace FluxErp\Rulesets\WarehouseBin;

use FluxErp\Models\WarehouseBin;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteWarehouseBinRuleset extends FluxRuleset
{
    protected static ?string $model = WarehouseBin::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => WarehouseBin::class]),
            ],
        ];
    }
}
