<?php

namespace FluxErp\Rulesets\Setting;

use FluxErp\Models\Setting;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateSettingRuleset extends FluxRuleset
{
    protected static ?string $model = Setting::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:settings,uuid',
            'key' => 'required|string|max:255|unique:settings,key',
            'model_type' => [
                'required_with:model_id',
                'string',
                'max:255',
                app(MorphClassExists::class),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                app(MorphExists::class),
            ],
            'settings' => 'required|array',
        ];
    }
}
