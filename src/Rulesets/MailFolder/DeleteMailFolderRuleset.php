<?php

namespace FluxErp\Rulesets\MailFolder;

use FluxErp\Models\MailFolder;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteMailFolderRuleset extends FluxRuleset
{
    protected static ?string $model = MailFolder::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(MailFolder::class),
            ],
        ];
    }
}
