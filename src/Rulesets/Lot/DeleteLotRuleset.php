<?php

namespace FluxErp\Rulesets\Lot;

use FluxErp\Models\Lot;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteLotRuleset extends FluxRuleset
{
    protected static ?string $model = Lot::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Lot::class]),
            ],
        ];
    }
}
