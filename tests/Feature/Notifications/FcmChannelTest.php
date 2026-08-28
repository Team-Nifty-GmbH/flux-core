<?php

use FluxErp\Enums\DevicePlatformEnum;
use FluxErp\Models\DeviceToken;
use FluxErp\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

beforeEach(function (): void {
    config(['flux.fcm.credentials' => __FILE__]);
});

function fcmChannelWith(Messaging $messaging): FcmChannel
{
    return new class($messaging) extends FcmChannel
    {
        public function __construct(private readonly Messaging $fake) {}

        protected function messaging(string $credentialsPath): Messaging
        {
            return $this->fake;
        }
    };
}

function fcmTestNotification(): Notification
{
    return new class() extends Notification
    {
        public function toFcm(object $notifiable): FcmNotification
        {
            return FcmNotification::create('Title', 'Body');
        }
    };
}

test('deactivates a device token that firebase no longer knows', function (): void {
    $deviceToken = DeviceToken::query()->create([
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
        'device_id' => 'dead-device',
        'token' => 'dead-token',
        'platform' => DevicePlatformEnum::ios,
        'is_active' => true,
    ]);

    $messaging = Mockery::mock(Messaging::class);
    $messaging->shouldReceive('send')
        ->once()
        ->andThrow(NotFound::becauseTokenNotFound('dead-token'));

    fcmChannelWith($messaging)->send($this->user, fcmTestNotification());

    expect($deviceToken->fresh()->is_active)->toBeFalse();
});

test('keeps a device token that firebase accepts', function (): void {
    $deviceToken = DeviceToken::query()->create([
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
        'device_id' => 'live-device',
        'token' => 'live-token',
        'platform' => DevicePlatformEnum::ios,
        'is_active' => true,
    ]);

    $messaging = Mockery::mock(Messaging::class);
    $messaging->shouldReceive('send')->once();

    fcmChannelWith($messaging)->send($this->user, fcmTestNotification());

    expect($deviceToken->fresh()->is_active)->toBeTrue();
});

test('resolves the firebase factory instead of a class in its own namespace', function (): void {
    $channel = new class() extends FcmChannel
    {
        public function resolveMessaging(string $credentialsPath): Messaging
        {
            return $this->messaging($credentialsPath);
        }
    };

    $failure = null;

    try {
        $channel->resolveMessaging(__FILE__);
    } catch (Throwable $exception) {
        $failure = $exception;
    }

    expect($failure)->not->toBeInstanceOf(Error::class);
});
