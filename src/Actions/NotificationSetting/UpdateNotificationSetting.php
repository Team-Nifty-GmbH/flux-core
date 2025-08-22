<?php

namespace FluxErp\Actions\NotificationSetting;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\NotificationSetting;
use FluxErp\Rulesets\NotificationSetting\UpdateNotificationSettingRuleset;
use FluxErp\Rulesets\NotificationSetting\UpdateUserNotificationSettingRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UpdateNotificationSetting extends FluxAction
{
    public static function models(): array
    {
        return [NotificationSetting::class];
    }

    protected function getRulesets(): string|array
    {
        return $this->getData('is_anonymous') ?
            UpdateNotificationSettingRuleset::class :
            UpdateUserNotificationSettingRuleset::class;
    }

    public function performAction(): Model
    {
        $notificationSetting = resolve_static(NotificationSetting::class, 'query')
            ->firstOrNew([
                'notifiable_type' => ! $this->getData('is_anonymous') ? Auth::user()->getMorphClass() : null,
                'notifiable_id' => ! $this->getData('is_anonymous') ? Auth::id() : null,
                'notification_type' => $this->getData('notification_type'),
                'channel' => config('notifications.channels.' . $this->getData('channel') . '.driver'),
            ]);

        $notificationSetting->is_active = $this->getData('is_active');

        if ($this->getData('is_anonymous')) {
            $notificationSetting->channel_value = $this->getData('channel_value');
        }

        $notificationSetting->save();

        return $notificationSetting->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['is_anonymous'] ??= false;
    }
}
