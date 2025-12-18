<?php

namespace FluxErp\Notifications\Channels;

use FluxErp\Models\DeviceToken;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FcmChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        $credentialsPath = config('flux.fcm.credentials');

        if (! method_exists($notification, 'toFcm') || ! $credentialsPath || ! file_exists($credentialsPath)) {
            return;
        }

        $fcmNotification = $notification->toFcm($notifiable);

        if (! $fcmNotification) {
            return;
        }

        $data = method_exists($notification, 'toFcmData')
            ? $notification->toFcmData($notifiable)
            : [];

        $deviceTokens = resolve_static(DeviceToken::class, 'query')
            ->whereMorphedTo('authenticatable', $notifiable)
            ->where('is_active', true)
            ->get([
                'token',
                'device_id',
                'device_name',
                'token',
            ]);

        if ($deviceTokens->isEmpty()) {
            return;
        }

        $messaging = new Factory()->withServiceAccount($credentialsPath)->createMessaging();

        foreach ($deviceTokens as $deviceToken) {
            try {
                $fcmMessage = CloudMessage::new()
                    ->withNotification($fcmNotification)
                    ->toToken($deviceToken->token);

                if ($data) {
                    $fcmMessage = $fcmMessage->withData($data);
                }

                $messaging->send($fcmMessage);
            } catch (MessagingException|FirebaseException $e) {
                logger()->error('Failed to send FCM notification', [
                    'user_id' => $notifiable->getKey(),
                    'device_id' => $deviceToken->device_id,
                    'error' => $e->getMessage(),
                ]);

                if (
                    str_contains($e->getMessage(), 'not-found')
                    || str_contains($e->getMessage(), 'invalid-registration-token')
                ) {
                    $deviceToken->update(['is_active' => false]);
                }
            }
        }
    }
}
