<?php

namespace FluxErp\Rulesets\Setting;

use FluxErp\Rules\ClassExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Settings\FluxSettings;

class UpdateSettingRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'settings_class' => [
                'required',
                'string',
                app(ClassExists::class, ['instanceOf' => FluxSettings::class]),
            ],
        ];
    }
}
