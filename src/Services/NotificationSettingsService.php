<?php

namespace FluxErp\Services;

use FluxErp\Actions\NotificationSetting\UpdateNotificationSetting;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Validation\ValidationException;

class NotificationSettingsService
{
    public function update(array $data, bool $isAnonymous = false): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $notificationSetting = UpdateNotificationSetting::make(
                        array_merge($item, ['is_anonymous' => $isAnonymous])
                    )->validate()->execute(),
                    additions: ['id' => $notificationSetting->id]
                );
            } catch (ValidationException $e) {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: $e->errors(),
                    additions: [
                        'id' => array_key_exists('id', $item) ? $item['id'] : null,
                    ]
                );

                unset($data[$key]);
            }
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'notification setting(s) updated',
            bulk: count($data) !== 0
        );
    }
}
