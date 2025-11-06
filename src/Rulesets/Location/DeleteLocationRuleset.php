<?php

namespace FluxErp\Rulesets\Location;

use FluxErp\Models\Location;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteLocationRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Location::class]),
            ],
        ];
    }
}
