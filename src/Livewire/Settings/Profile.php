<?php

namespace FluxErp\Livewire\Settings;

use Exception;
use FluxErp\Actions\NotificationSetting\UpdateNotificationSetting;
use FluxErp\Actions\User\UpdateUser;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
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

    public array $user = [];

    public function mount(): void
    {
        $this->user = auth()->user()->toArray();
        $this->avatar = auth()->user()->getFirstMediaUrl('avatar');
        $this->languages = app(Language::class)->all(['id', 'name'])->toArray();

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

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.settings.profile');
    }

    public function getRules(): array
    {
        return [
            'user.password' => 'confirmed',
        ];
    }

    public function save(): void
    {
        $this->validate();

        try {
            UpdateUser::make($this->user)
                ->checkPermission()
                ->validate()
                ->execute();
            $this->notification()->success(__(':model saved', ['model' => __('My Profile')]))->send();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

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
}
