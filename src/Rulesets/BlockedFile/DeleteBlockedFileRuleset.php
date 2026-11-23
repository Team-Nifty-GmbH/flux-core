<?php

namespace FluxErp\Rulesets\BlockedFile;

use FluxErp\Models\BlockedFile;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteBlockedFileRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => BlockedFile::class]),
            ],
        ];
    }
}
