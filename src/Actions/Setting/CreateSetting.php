<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Setting;
use FluxErp\Rulesets\Setting\CreateSettingRuleset;

class CreateSetting extends FluxAction
{
    public static function models(): array
    {
        return [Setting::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateSettingRuleset::class;
    }

    public function performAction(): Setting
    {
        $setting = app(Setting::class, ['attributes' => $this->data]);
        $setting->save();

        return $setting->fresh();
    }
}
