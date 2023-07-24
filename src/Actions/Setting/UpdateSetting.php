<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateSettingRequest;
use FluxErp\Models\Setting;
use Illuminate\Database\Eloquent\Model;

class UpdateSetting extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateSettingRequest())->rules();
    }

    public static function models(): array
    {
        return [Setting::class];
    }

    public function execute(): Model
    {
        $setting = Setting::query()
            ->whereKey($this->data['id'])
            ->first();

        $setting->settings = (object) $this->data['settings'];
        $setting->save();

        return $setting;
    }
}
