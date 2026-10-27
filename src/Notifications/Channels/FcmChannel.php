<?php

namespace FluxErp\Notifications\Channels;

use FluxErp\Models\DeviceToken;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class FcmChannel
{
    protected const int PAYLOAD_BUDGET = 3500;

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

        $title = Str::limit($fcmNotification->title() ?? '', 200, preserveWords: true);
        $imageUrl = $fcmNotification->imageUrl();
        $bodyBytes = static::PAYLOAD_BUDGET
            - strlen($title)
            - strlen($imageUrl ?? '')
            - strlen(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $fcmNotification = FcmNotification::create(
            $title,
            $this->limitBytes($fcmNotification->body() ?? '', $bodyBytes),
            $imageUrl
        );

        $deviceTokens = resolve_static(DeviceToken::class, 'query')
            ->whereMorphedTo('authenticatable', $notifiable)
            ->where('is_active', true)
            ->get([
                'id',
                'token',
                'device_id',
                'device_name',
            ]);

        if ($deviceTokens->isEmpty()) {
            return;
        }

        $messaging = $this->messaging($credentialsPath);

        foreach ($deviceTokens as $deviceToken) {
            try {
                $fcmMessage = CloudMessage::new()
                    ->withNotification($fcmNotification)
                    ->withToken($deviceToken->token);

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

                $message = strtolower($e->getMessage());

                // send() throws the api's NotFound with its bare message "Requested entity was not found."
                if (
                    $e instanceof NotFound
                    || str_contains($message, 'not-found')
                    || str_contains($message, 'invalid-registration-token')
                    || str_contains($message, 'notregistered')
                    || str_contains($message, 'not-registered')
                    || str_contains($message, 'unregistered')
                ) {
                    $deviceToken->update(['is_active' => false]);
                }
            }
        }
    }

    protected function limitBytes(string $value, int $bytes): string
    {
        if (strlen($value) <= $bytes) {
            return $value;
        }

        $cut = mb_strcut($value, 0, max(0, $bytes - 3), 'UTF-8');

        return Str::limit($value, mb_strlen($cut), preserveWords: true);
    }

    protected function messaging(string $credentialsPath): Messaging
    {
        return new Factory()->withServiceAccount($credentialsPath)->createMessaging();
    }
}
