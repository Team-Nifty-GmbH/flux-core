<?php

namespace FluxErp\Rulesets\Dashboard;

use FluxErp\Models\Dashboard;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateDashboardRuleset extends FluxRuleset
{
    protected static ?string $model = Dashboard::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Dashboard::class]),
            ],
            'authenticatable_type' => [
                'required_with:authenticatable_id',
                'nullable',
                'string',
                app(MorphClassExists::class),
            ],
            'authenticatable_id' => [
                'required_with:authenticatable_type',
                'nullable',
                'integer',
                app(MorphExists::class, ['modelAttribute' => 'authenticatable_type']),
            ],
            'name' => 'string',
            'is_public' => 'boolean',
        ];
    }
}
