<?php

namespace FluxErp\Rulesets\Translation;

use FluxErp\Models\LanguageLine;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteTranslationRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = LanguageLine::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LanguageLine::class]),
            ],
        ];
    }
}
