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

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->setData(array_merge(['is_anonymous' => false], $this->data));
    }

    public function performAction(): Model
    {
        $notificationSetting = resolve_static(NotificationSetting::class, 'query')
            ->firstOrNew([
                'notifiable_type' => ! $this->data['is_anonymous'] ? Auth::user()->getMorphClass() : null,
                'notifiable_id' => ! $this->data['is_anonymous'] ? Auth::id() : null,
                'notification_type' => $this->data['notification_type'],
                'channel' => config('notifications.channels.' . $this->data['channel'] . '.driver'),
            ]);

        $notificationSetting->is_active = $this->data['is_active'];

        if ($this->data['is_anonymous']) {
            $notificationSetting->channel_value = $this->data['channel_value'];
        }

        $notificationSetting->save();

        return $notificationSetting->fresh();
    }
}
