<?php

namespace FluxErp\Rulesets\NotificationSetting;

use FluxErp\Models\NotificationSetting;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

class UpdateNotificationSettingRuleset extends FluxRuleset
{
    protected static ?string $model = NotificationSetting::class;

    public function rules(): array
    {
        return [
            'notification_type' => [
                'required',
                'string',
            ],
            'channel' => [
                'required',
                'string',
                Rule::in(array_keys(config('notifications.channels'))),
            ],
            'channel_value' => 'present|array',
            'is_active' => 'required|boolean',
            'is_anonymous' => 'sometimes|required|boolean|accepted',
        ];
    }
}
