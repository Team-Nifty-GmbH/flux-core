<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Setting;
use FluxErp\Rulesets\Setting\UpdateSettingRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateSetting extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdateSettingRuleset::class;
    }

    public static function models(): array
    {
        return [Setting::class];
    }

    public function performAction(): Model
    {
        $setting = resolve_static(Setting::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $setting->settings = (object) $this->data['settings'];
        $setting->save();

        return $setting->fresh();
    }
}
