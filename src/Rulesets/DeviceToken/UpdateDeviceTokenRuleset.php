<?php

namespace FluxErp\Rulesets\DeviceToken;

use FluxErp\Enums\DevicePlatformEnum;
use FluxErp\Models\DeviceToken;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateDeviceTokenRuleset extends FluxRuleset
{
    protected static ?string $model = DeviceToken::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => DeviceToken::class]),
            ],
            'device_name' => 'nullable|string|max:255',
            'device_model' => 'nullable|string|max:255',
            'device_manufacturer' => 'nullable|string|max:255',
            'device_os_version' => 'nullable|string|max:255',
            'token' => 'sometimes|required|string',
            'platform' => [
                'sometimes',
                'required',
                'string',
                Rule::enum(DevicePlatformEnum::class),
            ],
            'is_active' => 'sometimes|required|boolean',
        ];
    }
}
