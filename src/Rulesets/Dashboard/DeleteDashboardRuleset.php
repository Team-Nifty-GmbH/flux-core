<?php

namespace FluxErp\Rulesets\Dashboard;

use FluxErp\Models\Dashboard;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteDashboardRuleset extends FluxRuleset
{
    protected static ?string $model = Dashboard::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Dashboard::class]),
            ],
        ];
    }
}
