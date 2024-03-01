<?php

namespace FluxErp\Rulesets\Language;

use FluxErp\Models\Language;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteLanguageRuleset extends FluxRuleset
{
    protected static ?string $model = Language::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Language::class),
            ],
        ];
    }
}
