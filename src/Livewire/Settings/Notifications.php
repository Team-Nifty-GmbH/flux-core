<?php

namespace FluxErp\Livewire\Settings;

use Closure;
use FluxErp\Models\NotificationSetting;
use FluxErp\Support\Notification\SubscribableNotification;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use ReflectionFunction;

class Notifications extends Component
{
    use Actions;

    public bool $detailModal = false;

    #[Locked]
    public array $dirtyNotificationChannels = [];

    public array $notification = [];

    public array $notificationChannels = [];

    public array $notifications = [];

    public array $notificationSettings = [];

    public string $notificationType = '';

    public function mount(): void
    {
        $this->notificationChannels = config('notifications.channels');
        foreach (Event::getFacadeRoot()->getRawListeners() as $event => $listeners) {
            foreach (Event::getFacadeRoot()->getListeners($event) as $listener) {
                /** @var Closure $listener */
                $notificationClass = data_get((new ReflectionFunction($listener))->getStaticVariables(), 'listener.0');
                if (is_subclass_of($notificationClass, SubscribableNotification::class)) {
                    $this->notifications[] = $notificationClass;
                }
            }
        }

        $notificationSettings = $this->notifications;
        $anonymousNotificationSettings = resolve_static(NotificationSetting::class, 'query')
            ->whereNull('notifiable_id')
            ->select([
                'id',
                'is_active',
                'notification_type',
                'channel',
            ])
            ->get()
            ->groupBy('notification_type')
            ->map(fn ($items) => $items->keyBy('channel'))
            ->toArray();

        foreach ($notificationSettings as $notificationSetting) {
            foreach ($this->notificationChannels as $channel) {
                $channelDriver = $channel['driver'] ?? false;
                $disabled = (($channel['method'] ?? false))
                    && ! method_exists($notificationSetting ?? false, $channel['method']);

                $userSetting = data_get(
                    $anonymousNotificationSettings,
                    $notificationSetting . '.' . $channelDriver);

                $this->notificationSettings[$notificationSetting][$channelDriver] =
                    [
                        'id' => data_get($userSetting, 'id'),
                        'name' => array_keys(
                            collect(config('notifications.channels'))
                                ->where('driver', $channelDriver)
                                ->toArray()
                        )[0],
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
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.notifications');
    }

    public function closeModal(): void
    {
        $this->detailModal = false;

        $this->skipRender();
    }

    public function save(): void
    {
        $this->getDirtyNotificationChannels($this->notification);

        array_walk($this->dirtyNotificationChannels, function (&$item): void {
            $item['channel_value'] = array_values(array_filter(array_unique($item['channel_value'])));
        });

        $anonymousNotificationSettings = array_values($this->dirtyNotificationChannels);

        $notificationSettingsService = app(NotificationSettingsService::class);
        $response = $notificationSettingsService->update($anonymousNotificationSettings, true);

        if ($response['status'] !== 200) {
            foreach ($response as $item) {
                if ($item['status'] === 422) {
                    $this->notification()->error(
                        title: __('Notification setting could not be saved'),
                        description: implode(', ', Arr::flatten($response['errors']))
                    )->send();
                }
            }
        }

        $this->dirtyNotificationChannels = [];
        $this->detailModal = false;

        $this->skipRender();
    }

    public function show($notification): void
    {
        $this->notificationType = $notification;
        $this->detailModal = true;
        $this->notification = data_get($this->notificationSettings, $notification);

        $notificationSettings = resolve_static(NotificationSetting::class, 'query')
            ->whereNull('notifiable_id')
            ->where('notification_type', $notification)
            ->get();

        foreach ($this->notification as $key => $value) {
            $this->notification[$key]['channel_value'] = $notificationSettings
                ->where('channel', $key)
                ->pluck('channel_value')
                ->first() ?: [];
        }

        $this->dirtyNotificationChannels = $this->notification;

        $this->skipRender();
    }

    #[Renderless]
    public function translate(string $key): string
    {
        return __(Str::headline(class_basename($key)));
    }

    private function getDirtyNotificationChannels(array $notification): void
    {
        $dirty = collect(Arr::dot($notification))->where(
            fn ($value, $key) => $value !== data_get($this->dirtyNotificationChannels, $key)
        )->toArray();

        $removed = collect(Arr::dot($this->dirtyNotificationChannels))->where(
            fn ($value, $key) => $value !== data_get($notification, $key)
        )->toArray();

        $dirty = array_merge($removed, $dirty);

        $this->dirtyNotificationChannels = [];
        foreach ($dirty as $key => $value) {
            $path = explode('.', $key);

            $data = data_get($notification, $path[0]);
            $data['channel'] = $data['name'];
            $data['notification_type'] = $this->notificationType;
            $this->dirtyNotificationChannels[$path[0]] = $data;
        }
    }
}
