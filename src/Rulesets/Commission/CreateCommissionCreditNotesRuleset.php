<?php

namespace FluxErp\Rulesets\Commission;

use FluxErp\Models\Commission;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateCommissionCreditNotesRuleset extends FluxRuleset
{
    protected static ?string $model = Commission::class;

    public function rules(): array
    {
        return [
            '*' => 'required|array',
            '*.id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Commission::class]),
            ],
        ];
    }
}
