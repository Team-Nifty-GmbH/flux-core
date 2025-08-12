<?php

namespace FluxErp\Livewire\Settings;

use Closure;
use Exception;
use FluxErp\Actions\NotificationSetting\UpdateNotificationSetting;
use FluxErp\Livewire\Forms\UserForm;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use FluxErp\Support\Notification\SubscribableNotification;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    public UserForm $user;

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
            $this->user->save();
            $this->notification()
                ->success(__(':model saved', ['model' => __('My Profile')]))
                ->send();
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
