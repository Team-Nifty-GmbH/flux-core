<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateSettingRequest;
use FluxErp\Models\Setting;

class CreateSetting extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateSettingRequest())->rules();
    }

    public static function models(): array
    {
        return [Setting::class];
    }

    public function execute(): Setting
    {
        $setting = new Setting($this->data);
        $setting->save();

        return $setting->fresh();
    }
}
