<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Models\Notification;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Notifications extends Component
{
    use Actions;

    public int $loaded = 0;

    public int $unread = 0;

    public function mount(): void
    {
        $this->unread = auth()->user()->unreadNotifications()->count();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.features.notifications');
    }

    #[Renderless]
    public function acceptNotify(Notification $notification): void
    {
        $accept = data_get($notification->data, 'accept');
        $notification->markAsRead();

        $this->js(<<<'JS'
            $slideClose('notifications-slide');
        JS);

        if (data_get($accept, 'url')) {
            $this->redirect(data_get($accept, 'url'), navigate: true);

            return;
        }
    }

    #[Renderless]
    public function closeNotifications(): void
    {
        $this->loaded = 0;

        $this->js(<<<'JS'
            removeAll();
        JS);
    }

    #[Renderless]
    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        $this->unread = 0;

        $this->js(<<<'JS'
            $slideClose('notifications-slide');
        JS);
    }

    #[Renderless]
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
        $index = array_search($notification->getKey(), array_column($this->notifications, 'id'));
        unset($this->notifications[$index]);

        $this->unread = $this->unread - 1;
        if (! $this->unread) {
            $this->js(<<<'JS'
                $slideClose('notifications-slide');
            JS);
        }
    }

    #[Renderless]
    public function sendNotify(array $notify): void
    {
        if (! is_null(data_get($notify, 'progress'))) {
            $notify['description'] = Blade::render(<<<'BLADE'
                {!! data_get($notify, 'description') !!}
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

        /** @var Notification $notification */
        $notification = resolve_static(Notification::class, 'query')
            ->whereKey(data_get($notify, 'id'))
            ->firstOrNew();

        if ($notification->exists) {
            $notification->markAsRead();
        } else {
            $notification->data = $notify;
            $notification->created_at = now();
        }

        $notification->toast($this)->id(data_get($notify, 'contextId'))->send();
    }

    #[Renderless]
    public function showNotifications(): void
    {

        auth()->user()
            ->unreadNotifications()
            ->offset($this->loaded)
            ->limit($this->loaded + 20)
            ?->get()
            ->each(function (Notification $notification): void {
                $notification
                    ->toast($this)
                    ->setEventName('toast-list')
                    ->persistent()
                    ->hook([
                        'close' => [
                            'method' => 'markAsRead',
                            'params' => $notification->getKey(),
                        ],
                    ])
                    ->send();
            });

        $this->loaded = $this->loaded + 20;
        $this->js(<<<'JS'
            $slideOpen('notifications-slide');
        JS);
    }
}
