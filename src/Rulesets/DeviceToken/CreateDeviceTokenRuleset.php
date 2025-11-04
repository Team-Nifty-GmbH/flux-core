<?php

namespace FluxErp\Rulesets\DeviceToken;

use FluxErp\Enums\DevicePlatformEnum;
use FluxErp\Models\DeviceToken;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class CreateDeviceTokenRuleset extends FluxRuleset
{
    protected static ?string $model = DeviceToken::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:device_tokens,uuid',
            'authenticatable_type' => [
                'required',
                'string',
                app(MorphClassExists::class),
            ],
            'authenticatable_id' => [
                'required',
                'integer',
                app(MorphExists::class, ['modelAttribute' => 'authenticatable_type']),
            ],
            'device_id' => 'required|string|max:255',
            'device_name' => 'nullable|string|max:255',
            'device_model' => 'nullable|string|max:255',
            'device_manufacturer' => 'nullable|string|max:255',
            'device_os_version' => 'nullable|string|max:255',
            'token' => 'required|string',
            'platform' => [
                'required',
                'string',
                Rule::enum(DevicePlatformEnum::class),
            ],
            'is_active' => 'sometimes|required|boolean',
        ];
    }
}
