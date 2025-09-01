<?php

namespace FluxErp\Livewire\Settings;

use Closure;
use Exception;
use FluxErp\Actions\NotificationSetting\UpdateNotificationSetting;
use FluxErp\Livewire\Forms\UserForm;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Support\Notification\SubscribableNotification;
use FluxErp\Notifications\WebPushTestNotification;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use ReflectionFunction;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Profile extends Component
{
    use Actions, WithFileUploads;

    public $avatar;

    public array $dirtyNotifications = [];

    public array $languages = [];

    public array $notificationChannels = [];

    public array $notifications = [];

    public array $notificationSettings = [];

    public array $pushSubscriptions = [];

    public UserForm $user;

    public array $webPushSupport = [];

    public function mount(): void
    {
        $this->user->fill(auth()->user());
        $this->avatar = auth()->user()->getFirstMediaUrl('avatar');
        $this->languages = resolve_static(Language::class, 'query')
            ->get(['id', 'name'])
            ->toArray();

        $this->notificationChannels = config('notifications.channels');
        foreach (Event::getFacadeRoot()->getRawListeners() as $event => $listeners) {
            foreach (Event::getFacadeRoot()->getListeners($event) as $listener) {
                /** @var Closure $listener */
                $notificationClass = data_get(
                    (new ReflectionFunction($listener))->getStaticVariables(),
                    'listener.0'
                );

                if (is_subclass_of($notificationClass, SubscribableNotification::class)) {
                    $this->notifications[] = $notificationClass;
                }
            }
        }

        $notificationSettings = $this->notifications;
        $userNotificationSettings = auth()->user()
            ->notificationSettings()
            ->select([
                'id',
                'notification_type',
                'channel',
                'is_active',
            ])
            ->get()
            ->groupBy('notification_type')
            ->map(fn ($items) => $items->keyBy('channel'))
            ->toArray() ?? [];

        foreach ($notificationSettings as $notificationSetting) {
            if (is_null($notificationSetting)) {
                continue;
            }

            foreach ($this->notificationChannels as $key => $channel) {
                $channelDriver = data_get($channel, 'driver') ?? false;
                $disabled = (data_get($channel, 'method') ?? false)
                    && ! method_exists($notificationSetting, data_get($channel, 'method'));

                $userSetting = data_get($userNotificationSettings, $notificationSetting . '.' . $channelDriver);

                $this->notificationSettings[$notificationSetting][$key] =
                    [
                        'id' => data_get($userSetting, 'id'),
                        'is_disabled' => $disabled,
                        'is_active' => ! $disabled
                            ? data_get(
                                $userSetting,
                                'is_active',
                                array_key_exists($channelDriver, array_flip($notificationSetting::defaultChannels()))
                            )
                            : false,
                    ];
            }
        }

        $this->notificationChannels = Arr::mapWithKeys(
            $this->notificationChannels,
            fn ($channel, $key) => [__(Str::headline($key)) => $channel]
        );

        $this->loadPushSubscriptions();
        $this->checkWebPushSupport();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.profile');
    }

    public function checkWebPushSupport(): void
    {
        $vapidSubject = config('webpush.vapid.subject');
        $validVapidSubject = ! blank($vapidSubject) &&
            (str_starts_with($vapidSubject, 'mailto:') || str_starts_with($vapidSubject, 'https://'));

        $this->webPushSupport = [
            'https' => request()->secure() || request()->getHost() === 'localhost',
            'vapidKey' => ! blank(config('webpush.vapid.public_key')),
            'vapidSubject' => $validVapidSubject,
            'isSafari' => str_contains(request()->header('User-Agent', ''), 'Safari') &&
                         ! str_contains(request()->header('User-Agent', ''), 'Chrome'),
        ];
    }

    #[Renderless]
    public function deletePushSubscription(int $id): void
    {
        try {
            auth()->user()->pushSubscriptions()->whereKey($id)->delete();
            $this->loadPushSubscriptions();

            $this->notification()
                ->success(__('Push subscription deleted'))
                ->send();
        } catch (Exception $e) {
            exception_to_notifications($e, $this);
        }
    }

    public function getRules(): array
    {
        return [
            'user.password' => 'confirmed',
        ];
    }

    #[Renderless]
    public function loadPushSubscriptions(): void
    {
        $this->pushSubscriptions = auth()->user()
            ->pushSubscriptions()
            ->select(['id', 'endpoint', 'created_at'])
            ->get()
            ->map(function ($subscription) {
                return [
                    'id' => $subscription->id,
                    'endpoint' => $subscription->endpoint,
                    'browser' => $this->detectBrowserFromEndpoint($subscription->endpoint),
                    'created_at' => $subscription->created_at->format('Y-m-d H:i'),
                ];
            })
            ->toArray();
    }

    #[On('push-error')]
    #[Renderless]
    public function onPushError(string $message): void
    {
        $this->notification()
            ->error($message)
            ->send();
    }

    #[On('push-subscription-updated')]
    #[Renderless]
    public function onPushSubscriptionUpdated(): void
    {
        $this->loadPushSubscriptions();
        $this->notification()
            ->success(__('Web Push activated successfully'))
            ->send();
    }

    public function save(): void
    {
        $this->validate();

        try {
            $this->user->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->notification()->success(__(':model saved', ['model' => __('My Profile')]))->send();

        $dirtyNotifications = [];
        foreach ($this->dirtyNotifications as $key) {
            $key = basename($key, '.is_active');
            $notification = explode('.', $key);
            $data = data_get($this->notificationSettings, $key);
            $data['notification_type'] = $notification[0];
            $data['channel'] = $notification[1];

            $dirtyNotifications[$key] = $data;
        }

        foreach ($dirtyNotifications as $notificationSetting) {
            try {
                UpdateNotificationSetting::make($notificationSetting)
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $this->skipRender();
    }

    public function sendTestNotification(): void
    {
        try {
            if (! auth()->user()->pushSubscriptions()->exists()) {
                $this->notification()
                    ->error(__('No active push subscriptions found. Please activate Web Push first.'))
                    ->send();

                return;
            }

            auth()->user()->notify(new WebPushTestNotification());

            $this->notification()
                ->success(__('Test notification sent! Check your browser notifications.'))
                ->send();
        } catch (Exception $e) {
            exception_to_notifications($e, $this);
        }
    }

    public function updatedAvatar(): void
    {
        $this->collection = 'avatar';
        try {
            $response = $this->saveFileUploadsToMediaLibrary(
                'avatar',
                auth()->id(),
                app(User::class)->getMorphClass()
            );
        } catch (Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->avatar = $response[0]['original_url'];
    }

    public function updatingNotificationSettings($value, $key): void
    {
        $this->dirtyNotifications[] = $key;
    }

    protected function detectBrowserFromEndpoint(string $endpoint): string
    {
        if (str_contains($endpoint, 'googleapis.com')) {
            return __('Chrome/Edge');
        } elseif (str_contains($endpoint, 'mozilla.com')) {
            return __('Firefox');
        } elseif (str_contains($endpoint, 'apple.com')) {
            return __('Safari');
        }

        return __('Unknown');
    }
}
