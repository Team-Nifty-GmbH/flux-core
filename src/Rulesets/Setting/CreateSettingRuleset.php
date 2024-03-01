<?php

namespace FluxErp\Rulesets\Setting;

use FluxErp\Models\Setting;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateSettingRuleset extends FluxRuleset
{
    protected static ?string $model = Setting::class;

    public function rules(): array
    {
        return [
            'uuid' => 'string|uuid|unique:settings,uuid',
            'key' => 'required|string|unique:settings,key',
            'model_type' => [
                'required',
                'string',
                new MorphClassExists(),
            ],
            'model_id' => [
                'required',
                'integer',
                new MorphExists(),
            ],
            'settings' => 'required|array',
        ];
    }
}
