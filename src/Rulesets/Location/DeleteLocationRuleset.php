<?php

namespace FluxErp\Rulesets\Location;

use FluxErp\Models\Location;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class DeleteLocationRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Location::class),
            ],
        ];
    }
}