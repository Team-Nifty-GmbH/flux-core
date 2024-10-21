<?php

namespace FluxErp\Rulesets\Dashboard;

use FluxErp\Models\Dashboard;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateDashboardRuleset extends FluxRuleset
{
    protected static ?string $model = Dashboard::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:dashboards,uuid',
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
            'copy_from_dashboard_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Dashboard::class])
                    ->where('is_public', true)
                    ->where('authenticatable_type', auth()->user()->getMorphClass()),
            ],
            'name' => 'required_without:copy_from_dashboard_id|string',
            'is_public' => 'exclude_if:copy_from_dashboard_id,boolean',
        ];
    }
}
