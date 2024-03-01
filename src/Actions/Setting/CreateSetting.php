<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Setting;
use FluxErp\Rulesets\Setting\CreateSettingRuleset;

class CreateSetting extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateSettingRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Setting::class];
    }

    public function performAction(): Setting
    {
        $setting = app(Setting::class, ['attributes' => $this->data]);
        $setting->save();

        return $setting->fresh();
    }
}
