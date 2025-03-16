<?php

namespace FluxErp\Rulesets\MailFolder;

use FluxErp\Models\MailFolder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteMailFolderRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = MailFolder::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => MailFolder::class]),
            ],
        ];
    }
}
