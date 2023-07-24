<?php

namespace FluxErp\Actions\NotificationSetting;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateNotificationSettingsRequest;
use FluxErp\Http\Requests\UpdateUserNotificationSettingsRequest;
use FluxErp\Models\NotificationSetting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UpdateNotificationSetting extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct(array_merge(['is_anonymous' => false], $data[0] ?? []));
        $this->rules = $this->data['is_anonymous'] ?
            (new UpdateNotificationSettingsRequest())->rules() : (new UpdateUserNotificationSettingsRequest())->rules();
    }

    public static function models(): array
    {
        return [NotificationSetting::class];
    }

    public function execute(): Model
    {
        $notificationSetting = NotificationSetting::query()
            ->firstOrNew([
                'notifiable_type' => ! $this->data['is_anonymous'] ? Auth::user()->getMorphClass() : null,
                'notifiable_id' => ! $this->data['is_anonymous'] ? Auth::id() : null,
                'notification_type' => $this->data['notification_type'],
                'channel' => config('notifications.channels.' . $this->data['channel'] . '.driver'),
            ], [
                'is_active' => $this->data['is_active'],
            ]);

        if ($this->data['is_anonymous']) {
            $notificationSetting->channel_value = $this->data['channel_value'];
        }

        $notificationSetting->save();

        return $notificationSetting;
    }
}
