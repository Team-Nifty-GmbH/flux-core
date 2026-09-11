<?php

namespace FluxErp\Rulesets\Resource;

use FluxErp\Models\Resource;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteResourceRuleset extends FluxRuleset
{
    protected static ?string $model = Resource::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Resource::class]),
            ],
        ];
    }
}
