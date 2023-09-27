<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Http\Requests\UpdateUserRequest;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Services\NotificationSettingsService;
use FluxErp\Services\UserService;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Livewire\Component;
use WireUi\Traits\Actions;

class Profile extends Component
{
    use Actions, WithFileUploads;

    public array $user = [];

    public array $languages = [];

    public array $notifications = [];

    public array $notificationSettings = [];

    public array $notificationChannels = [];

    public array $dirtyNotifications = [];

    public $avatar;

    public function mount(): void
    {
        $this->user = auth()->user()->toArray();
        $this->avatar = auth()->user()->getFirstMediaUrl('avatar');
        $this->languages = Language::all(['id', 'name'])->toArray();

        $this->notificationChannels = config('notifications.channels');
        $this->notifications = config('notifications.model_notifications');

        $notificationSettings = data_get($this->notifications, '*.*');
        $userNotificationSettings = auth()->user()
            ->notificationSettings()
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
            foreach ($this->notificationChannels as $key => $channel) {
                $channelDriver = $channel['driver'] ?? false;
                $disabled = ($channel['method'] ?? false)
                    && ! method_exists($notificationSetting ?? false, $channel['method']);

                $userSetting = data_get(
                    $userNotificationSettings,
                    $notificationSetting . '.' . $channelDriver);

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
    }

    public function updatingNotificationSettings(array $notificationSettings): void
    {
        $dirty = array_diff_assoc(Arr::dot($this->notificationSettings), Arr::dot($notificationSettings));

        foreach ($dirty as $key => $value) {
            $key = basename($key, '.is_active');
            $notification = explode('.', $key);
            $data = data_get($notificationSettings, $key);
            $data['notification_type'] = $notification[0];
            $data['channel'] = $notification[1];

            $this->dirtyNotifications[$key] = $data;
        }
    }

    public function getRules(): array
    {
        $rules = (new UpdateUserRequest())->getRules($this->user);

        $rules['password'][] = 'confirmed';

        return Arr::prependKeysWith($rules, 'user.');
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.profile');
    }

    public function updatedAvatar(): void
    {
        $this->collection = 'avatar';
        try {
            $response = $this->saveFileUploadsToMediaLibrary('avatar', auth()->id(), User::class);
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->avatar = $response[0]->getUrl();
    }

    public function save(): void
    {
        if (! user_can('my-profile.{id?}.get') || auth()->id() !== $this->user['id']) {
            return;
        }

        $this->validate();

        $notificationSettingsService = new NotificationSettingsService();
        $notificationSettingsService->update(array_values($this->dirtyNotifications));

        $userService = new UserService();
        $response = $userService->update($this->user);

        if ($response['status'] < 300) {
            $this->notification()->success(__('Profile saved successful.'));
        } else {
            $this->notification()->error(
                implode(',', array_keys($response['errors'])),
                implode(', ', Arr::dot($response['errors']))
            );
        }

        $this->skipRender();
    }
}
