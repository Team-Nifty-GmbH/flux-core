<?php

namespace FluxErp\Listeners;

use FluxErp\Actions\DeviceToken\CreateDeviceToken;
use FluxErp\Actions\DeviceToken\UpdateDeviceToken;
use FluxErp\Models\DeviceToken;
use Illuminate\Auth\Events\Login;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RegisterMobilePushToken
{
    public function handle(Login $event): void
    {
        if (! $fcmToken = session('pending_fcm_token')) {
            return;
        }

        $deviceId = session('pending_fcm_device_id') ?: str()->uuid()->toString();

        $deviceData = [
            'device_name' => session('pending_fcm_device_name'),
            'device_model' => session('pending_fcm_device_model'),
            'device_manufacturer' => session('pending_fcm_device_manufacturer'),
            'device_os_version' => session('pending_fcm_device_os_version'),
            'token' => $fcmToken,
            'platform' => session('pending_fcm_platform'),
            'is_active' => true,
        ];

        try {
            if ($existingTokenId = resolve_static(DeviceToken::class, 'query')
                ->where('device_id', $deviceId)
                ->whereMorphedTo('authenticatable', $event->user)
                ->value('id')
            ) {
                UpdateDeviceToken::make(array_merge($deviceData, ['id' => $existingTokenId]))
                    ->validate()
                    ->execute();
            } else {
                CreateDeviceToken::make(
                    array_merge(
                        [
                            'authenticatable_type' => $event->user->getMorphClass(),
                            'authenticatable_id' => $event->user->getKey(),
                            'device_id' => $deviceId,
                        ],
                        $deviceData
                    )
                )
                    ->validate()
                    ->execute();
            }
        } catch (ValidationException|UnauthorizedException $e) {
            report($e);
        }

        session()->forget([
            'pending_fcm_token',
            'pending_fcm_platform',
            'pending_fcm_device_id',
            'pending_fcm_device_name',
            'pending_fcm_device_model',
            'pending_fcm_device_manufacturer',
            'pending_fcm_device_os_version',
        ]);
    }
}
