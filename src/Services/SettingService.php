<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Setting;

class SettingService
{
    public function create(array $data): Setting
    {
        $setting = new Setting($data);
        $setting->save();

        return $setting;
    }

    public function update(array $data): array
    {
        $setting = Setting::query()
            ->whereKey($data['id'])
            ->first();

        $setting->settings = (object) $data['settings'];
        $setting->save();

        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            data: $setting,
            statusMessage: 'setting updated'
        );
    }
}
