<?php

namespace FluxErp\Rulesets\Setting;

use FluxErp\Models\Setting;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateSettingRuleset extends FluxRuleset
{
    protected static ?string $model = Setting::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Setting::class),
            ],
            'settings' => 'required|array',
        ];
    }
}
