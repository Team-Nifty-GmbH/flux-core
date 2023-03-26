<?php

namespace FluxErp\Http\Livewire\Features;

use FluxErp\Models\Notification;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;
use WireUi\Traits\Actions;

class Notifications extends Component
{
    use Actions;

    public array $notifications = [];

    public int $unread = 0;

    public bool $showNotifications = false;

    public function getListeners(): array
    {
        return [
            'echo-private:'
            . auth()->user()->broadcastChannel(false) .
            ',.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated' => 'sendNotify',
        ];
    }

    public function mount(): void
    {
        $this->unread = auth()->user()->unreadNotifications()->count();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.features.notifications');
    }

    public function sendNotify(array $notify): void
    {
        $notify['description'] = Str::of($notify['description'])->limit(75);

        $this->notification()->confirm($notify);

        $this->notifications[] = $notify;
        Notification::query()->whereKey($notify['id'])->first()?->markAsRead();

        $this->skipRender();
    }

    public function showNotifications(): void
    {
        $this->showNotifications = true;

        $this->getNotification();

        $this->skipRender();
    }

    public function getNotification(): array
    {
        auth()->user()
            ->unreadNotifications()
            ->limit(20)
            ?->get()
            ->each(function ($notification) {
                $notificationOptions = $notification->data;
                $notificationOptions['notification_id'] = $notification->id;
                $notificationOptions['timeout'] = 0;
                $notificationOptions['rejectLabel'] = 0;
                $notificationOptions['onClose'] = [
                    'method' => 'markAsRead',
                    'params' => $notification->id,
                ];
                $this->notifications[] = $notificationOptions;
            }
            );

        return $this->notifications;
    }

    public function markAsRead(string $id): void
    {
        Notification::query()->whereKey($id)->first()?->markAsRead();
        $index = array_search($id, array_column($this->notifications, 'id'));
        unset($this->notifications[$index]);

        $this->unread = $this->unread - 1;
        if (! $this->unread) {
            $this->showNotifications = false;
        }

        $this->skipRender();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->unread = 0;

        $this->skipRender();
    }
}
