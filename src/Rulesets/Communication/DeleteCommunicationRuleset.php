<?php

namespace FluxErp\Rulesets\Communication;

use FluxErp\Models\Communication;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCommunicationRuleset extends FluxRuleset
{
    protected static ?string $model = Communication::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Communication::class]),
            ],
        ];
    }
}
