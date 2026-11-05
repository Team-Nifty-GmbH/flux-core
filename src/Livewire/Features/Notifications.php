<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Models\Notification;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Notifications extends Component
{
    use Actions;

    #[Locked]
    public ?string $cursor = null;

    #[Locked]
    public bool $hasMore = true;

    public int $unread = 0;

    public function mount(): void
    {
        $this->unread = auth()->user()->unreadNotifications()->count();
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.features.notifications');
    }

    #[On('notifications-changed')]
    public function refreshUnread(): void
    {
        $this->unread = auth()->user()->unreadNotifications()->count();
    }

    #[Renderless]
    public function acceptNotify(string $notification): void
    {
        $notification = auth()->user()->notifications()->whereKey($notification)->first();

        if (! $notification) {
            return;
        }

        $accept = data_get($notification->data, 'accept');
        $notification->markAsRead();
        $this->unread = max(0, $this->unread - 1);
        $this->dispatch('notifications-changed');

        $this->js(<<<'JS'
            $tsui.close.slide('notifications-slide');
        JS);

        if (data_get($accept, 'url')) {
            $this->redirect(data_get($accept, 'url'), navigate: ! data_get($accept, 'download'));
        }
    }

    #[Renderless]
    public function closeNotifications(): void
    {
        $this->cursor = null;
        $this->hasMore = true;
    }

    #[Renderless]
    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        $this->unread = 0;
        $this->dispatch('notifications-changed');

        $this->js(<<<'JS'
            $tsui.close.slide('notifications-slide');
        JS);
    }

    #[Renderless]
    public function markAsRead(string $notification): void
    {
        $notification = auth()->user()->notifications()->whereKey($notification)->first();

        if (! $notification) {
            return;
        }

        $notification->markAsRead();

        $this->unread = max(0, $this->unread - 1);
        $this->dispatch('notifications-changed');
        if (! $this->unread) {
            $this->js(<<<'JS'
                $tsui.close.slide('notifications-slide');
            JS);
        }
    }

    #[Renderless]
    public function sendNotify(array $notify): void
    {
        if (data_get($notify, 'accept') || data_get($notify, 'reject')) {
            $notify['timeout'] = 0;
        }

        /** @var Notification $notification */
        $notification = auth()->user()
            ->notifications()
            ->whereKey(data_get($notify, 'id'))
            ->first() ?? app(Notification::class);

        if (! $notification->exists) {
            $notification->data = $notify;
            $notification->created_at = now();
        } elseif (data_get($notify, 'markAsRead')) {
            $notification->markAsRead();
        }

        $notification->toast($this)
            ->id(data_get($notify, 'contextId'))
            ->send();

        $this->dispatch('notifications-changed');
    }

    #[Renderless]
    public function showNotifications(): void
    {
        if (! $this->hasMore) {
            return;
        }

        $notifications = auth()->user()
            ->unreadNotifications()
            ->orderByDesc('id')
            ->cursorPaginate(perPage: 20, cursor: $this->cursor);

        $notifications->each(function (Notification $notification): void {
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

        $this->cursor = $notifications->nextCursor()?->encode();
        $this->hasMore = $notifications->hasMorePages();
    }
}
