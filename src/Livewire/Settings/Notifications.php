<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Models\NotificationSetting;
use FluxErp\Services\NotificationSettingsService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;
use Livewire\Component;
use WireUi\Traits\Actions;

class Notifications extends Component
{
    use Actions;

    public array $notifications = [];

    public array $notificationSettings = [];

    public array $notification = [];

    public string $notificationType = '';

    #[Locked]
    public array $dirtyNotificationChannels = [];

    public array $notificationChannels = [];

    public bool $detailModal = false;

    public function mount(): void
    {
        $this->notificationChannels = config('notifications.channels');
        $this->notifications = config('notifications.model_notifications');

        $notificationSettings = data_get($this->notifications, '*.*');
        $anonymousNotificationSettings = app(NotificationSetting::class)->query()
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

    public function save(): void
    {
        $this->getDirtyNotificationChannels($this->notification);

        array_walk($this->dirtyNotificationChannels, function (&$item) {
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
                    );
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

        $notificationSettings = app(NotificationSetting::class)->query()
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

    public function closeModal(): void
    {
        $this->detailModal = false;

        $this->skipRender();
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
