<?php

namespace FluxErp\Rulesets\NotificationSetting;

class UpdateUserNotificationSettingRuleset extends UpdateNotificationSettingRuleset
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'channel_value' => 'exclude',
                'is_anonymous' => 'sometimes|required|boolean|declined',
            ],
        );
    }
}
