<?php

namespace FluxErp\Actions\Setting;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateSettingRequest;
use FluxErp\Models\Setting;
use Illuminate\Database\Eloquent\Model;

class UpdateSetting extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateSettingRequest())->rules();
    }

    public static function models(): array
    {
        return [Setting::class];
    }

    public function performAction(): Model
    {
        $setting = Setting::query()
            ->whereKey($this->data['id'])
            ->first();

        $setting->settings = (object) $this->data['settings'];
        $setting->save();

        return $setting->fresh();
    }
}
