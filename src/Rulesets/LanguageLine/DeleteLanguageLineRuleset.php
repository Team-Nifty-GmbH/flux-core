<?php

namespace FluxErp\Rulesets\LanguageLine;

use FluxErp\Models\LanguageLine;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteLanguageLineRuleset extends FluxRuleset
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
