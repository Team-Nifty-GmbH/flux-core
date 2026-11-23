<?php

namespace FluxErp\Rulesets\BlockedFile;

use FluxErp\Models\BlockedFile;
use FluxErp\Models\Media;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class BlockFileRuleset extends FluxRuleset
{
    protected static ?string $model = BlockedFile::class;

    public function rules(): array
    {
        return [
            'media_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Media::class]),
            ],
        ];
    }
}
