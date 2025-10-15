<?php

namespace FluxErp\Rulesets\Setting;

use FluxErp\Rulesets\FluxRuleset;

class UpdateSettingRuleset extends FluxRuleset
{
    protected static ?string $model = null;

    public function rules(): array
    {
        return [
            'settings_class' => 'required|string',
        ];
    }
}
