<?php

namespace FluxErp\Rulesets\Commission;

use FluxErp\Models\Commission;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateCommissionRuleset extends FluxRuleset
{
    protected static ?string $model = Commission::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Commission::class]),
            ],
            'commission' => 'required|numeric',
        ];
    }
}
