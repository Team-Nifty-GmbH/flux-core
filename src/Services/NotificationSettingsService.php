<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateNotificationSettingsRequest;
use FluxErp\Http\Requests\UpdateUserNotificationSettingsRequest;
use FluxErp\Models\NotificationSetting;
use Illuminate\Support\Facades\Auth;

class NotificationSettingsService
{
    public function update(array $data, bool $isAnonymous = false): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: ! $isAnonymous ?
                new UpdateUserNotificationSettingsRequest() : new UpdateNotificationSettingsRequest()
        );

        foreach ($data as $item) {
            $notificationSetting = NotificationSetting::query()
                ->firstOrNew([
                    'notifiable_type' => ! $isAnonymous ? Auth::user()->getMorphClass() : null,
                    'notifiable_id' => ! $isAnonymous ? Auth::id() : null,
                    'notification_type' => $item['notification_type'],
                    'channel' => config('notifications.channels.' . $item['channel'] . '.driver'),
                ], [
                    'is_active' => $item['is_active'],
                ]);

            if ($isAnonymous) {
                $notificationSetting->channel_value = $item['channel_value'];
            }

            $notificationSetting->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $notificationSetting,
                additions: ['id' => $notificationSetting->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'notification settings updated',
            bulk: count($data) !== 0
        );
    }
}
