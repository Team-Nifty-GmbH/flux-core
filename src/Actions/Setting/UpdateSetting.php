<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Setting;
use FluxErp\Rulesets\Setting\UpdateSettingRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateSetting extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateSettingRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Setting::class];
    }

    public function performAction(): Model
    {
        $setting = app(Setting::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $setting->settings = (object) $this->data['settings'];
        $setting->save();

        return $setting->fresh();
    }
}
