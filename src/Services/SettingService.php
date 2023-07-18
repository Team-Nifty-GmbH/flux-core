<?php

namespace FluxErp\Services;

use FluxErp\Actions\Setting\CreateSetting;
use FluxErp\Actions\Setting\UpdateSetting;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Setting;

class SettingService
{
    public function create(array $data): Setting
    {
        return CreateSetting::make($data)->execute();
    }

    public function update(array $data): array
    {
        return ResponseHelper::createArrayResponse(
            statusCode: 200,
            data: UpdateSetting::make($data)->execute(),
            statusMessage: 'setting updated'
        );
    }
}
