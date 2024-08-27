<?php

namespace FluxErp\Rulesets\Communication;

use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Traits\Communicatable;

class CommunicatablesRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'communicatables' => 'array',
            'communicatables.*.id' => [
                'integer',
                app(ModelExists::class, ['model' => \FluxErp\Models\Pivots\Communicatable::class]),
            ],
            'communicatables.*.communicatable_type' => [
                'required_with:communicatable_id',
                'string',
                app(MorphClassExists::class, ['uses' => Communicatable::class]),
            ],
            'communicatables.*.communicatable_id' => [
                'required_with:communicatable_type',
                'integer',
                app(MorphExists::class, ['modelAttribute' => 'communicatable_type']),
            ],
        ];
    }
}
