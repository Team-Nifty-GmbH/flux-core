<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateSettingRequest;
use FluxErp\Models\Setting;

class CreateSetting extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateSettingRequest())->rules();
    }

    public static function models(): array
    {
        return [Setting::class];
    }

    public function performAction(): Setting
    {
        $setting = new Setting($this->data);
        $setting->save();

        return $setting->fresh();
    }
}
