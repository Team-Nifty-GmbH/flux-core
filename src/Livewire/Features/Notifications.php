<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Models\Notification;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Notifications extends Component
{
    use Actions;

    public array $notifications = [];

    public int $unread = 0;

    public bool $showNotifications = false;

    public function mount(): void
    {
        $this->unread = auth()->user()->unreadNotifications()->count();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.features.notifications');
    }

    #[Renderless]
    public function sendNotify(array $notify): void
    {
        $notify['description'] = Str::of(data_get($notify, 'description'))->limit(75);

        if (! is_null(data_get($notify, 'progress'))) {
            $notify['description'] = Blade::render(<<<'BLADE'
                {!! $notify['description'] !!}
                <div class="flex gap-1.5 items-center h-6">
                    <div class="overflow-hidden h-2 text-xs flex rounded bg-gray-200 dark:bg-gray-700 w-full">
                        <div x-bind:style="{width: notification.progress * 100 + '%'}" class="transition-all shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500 dark:bg-indigo-700"></div>
                    </div>
                </div>
            BLADE, ['notify' => $notify]);
        }

        if (data_get($notify, 'accept') || data_get($notify, 'reject')) {
            $notify['timeout'] = 0;
        }

        if (data_get($notify, 'accept')) {
            $this->notification()->confirm($notify);
        } else {
            $this->notification()->send($notify);
        }

        $this->notifications[data_get($notify, 'id')] = $notify;
        resolve_static(Notification::class, 'query')->whereKey($notify['id'])->first()?->markAsRead();
    }

    #[Renderless]
    public function showNotifications(): void
    {
        $this->showNotifications = true;

        $this->getNotification();
    }

    #[Renderless]
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
            });

        return $this->notifications;
    }

    #[Renderless]
    public function markAsRead(string $id): void
    {
        resolve_static(Notification::class, 'query')->whereKey($id)->first()?->markAsRead();
        $index = array_search($id, array_column($this->notifications, 'id'));
        unset($this->notifications[$index]);

        $this->unread = $this->unread - 1;
        if (! $this->unread) {
            $this->showNotifications = false;
        }
    }

    #[Renderless]
    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->unread = 0;
    }
}
