<?php

use FluxErp\Enums\DevicePlatformEnum;
use FluxErp\Models\DeviceToken;
use FluxErp\Notifications\Channels\FcmChannel;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\Messaging\NotFound;
use Kreait\Firebase\Messaging\CloudMessage;
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

test('deactivates a device token when send reports the bare not found error', function (): void {
    $deviceToken = DeviceToken::query()->create([
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
        'device_id' => 'dead-device',
        'token' => 'dead-token',
        'platform' => DevicePlatformEnum::ios,
        'is_active' => true,
    ]);

    // Messaging::send() rethrows the converted api error as is, with the api's own message.
    $messaging = Mockery::mock(Messaging::class);
    $messaging->shouldReceive('send')
        ->once()
        ->andThrow(new NotFound('Requested entity was not found.'));

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

test('shortens title and body so the message fits into the apns payload limit', function (): void {
    DeviceToken::query()->create([
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
        'device_id' => 'live-device',
        'token' => 'live-token',
        'platform' => DevicePlatformEnum::ios,
        'is_active' => true,
    ]);

    $sent = null;
    $messaging = Mockery::mock(Messaging::class);
    $messaging->shouldReceive('send')
        ->once()
        ->andReturnUsing(function (CloudMessage $message) use (&$sent): array {
            $sent = $message->jsonSerialize();

            return [];
        });

    fcmChannelWith($messaging)->send($this->user, new class() extends Notification
    {
        public function toFcm(object $notifiable): FcmNotification
        {
            return FcmNotification::create(str_repeat('t', 500), str_repeat('b', 5000), 'https://example.com/image.png');
        }
    });

    expect(data_get($sent, 'notification.title'))->toStartWith('ttt')
        ->and(strlen(data_get($sent, 'notification.title')))->toBeLessThanOrEqual(203)
        ->and(data_get($sent, 'notification.body'))->toStartWith('bbb')
        ->and(strlen(json_encode($sent)))->toBeLessThan(4096)
        ->and(data_get($sent, 'notification.image'))->toBe('https://example.com/image.png');
});

test('counts multibyte text and the data payload against the apns payload limit', function (): void {
    DeviceToken::query()->create([
        'authenticatable_type' => morph_alias($this->user::class),
        'authenticatable_id' => $this->user->getKey(),
        'device_id' => 'live-device',
        'token' => 'live-token',
        'platform' => DevicePlatformEnum::ios,
        'is_active' => true,
    ]);

    $sent = null;
    $messaging = Mockery::mock(Messaging::class);
    $messaging->shouldReceive('send')
        ->once()
        ->andReturnUsing(function (CloudMessage $message) use (&$sent): array {
            $sent = $message->jsonSerialize();

            return [];
        });

    $path = '/orders/' . str_repeat('a', 2500);

    fcmChannelWith($messaging)->send($this->user, new class($path) extends Notification
    {
        public function __construct(private readonly string $path) {}

        public function toFcm(object $notifiable): FcmNotification
        {
            return FcmNotification::create(str_repeat('Ü', 500), str_repeat('Größe ', 1500));
        }

        public function toFcmData(object $notifiable): array
        {
            return ['url' => 'https://example.com', 'path' => $this->path];
        }
    });

    expect(strlen(json_encode($sent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)))->toBeLessThan(4096)
        ->and(data_get($sent, 'data.path'))->toBe($path)
        ->and(data_get($sent, 'notification.body'))->toStartWith('Größe')
        ->and(data_get($sent, 'notification.body'))->toEndWith('Größe...');
});
