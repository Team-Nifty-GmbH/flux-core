<?php

namespace FluxErp\Livewire;

use FluxErp\Facades\Menu;
use FluxErp\Models\Notification;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class Navigation extends Component
{
    public function mount(): void
    {
        $this->markAreaRead(Route::currentRouteName());
    }

    public function render(): View|Factory|Application
    {
        $navigations = $this->getMenu();

        return view('flux::livewire.navigation', [
            'navigations' => $navigations,
            'visits' => $this->getVisits(),
            'favorites' => $this->getFavorites(),
            'notificationCounts' => $this->getNotificationCounts(),
            'childNotificationCounts' => $this->getChildNotificationCounts($navigations),
        ]);
    }

    #[On('notifications-changed')]
    public function refreshNotificationCounts(): void
    {
        // Re-render so the per-area badges pick up the latest unread counts.
    }

    public function addFavorite(string $url, ?string $name = null): void
    {
        if (! method_exists(auth()->user(), 'favorites')) {
            return;
        }

        auth()->user()
            ->favorites()
            ->create([
                'name' => $name ?: $url,
                'url' => $url,
            ]);
    }

    public function deleteFavorite(int $id): void
    {
        if (! method_exists(auth()->user(), 'favorites')) {
            return;
        }

        auth()->user()
            ->favorites()
            ->whereKey($id)
            ->delete();
    }

    protected function markAreaRead(?string $routeName): void
    {
        $user = auth()->user();

        if (! method_exists($user, 'unreadNotifications')) {
            return;
        }

        $area = Str::before($routeName ?? '', '.') ?: null;

        if (blank($area)) {
            return;
        }

        $ids = $user->unreadNotifications
            ->filter(fn (Notification $notification): bool => $notification->menuArea() === $area)
            ->modelKeys();

        if (blank($ids)) {
            return;
        }

        $user->unreadNotifications()
            ->whereKey($ids)
            ->update(['read_at' => now()]);

        $this->dispatch('notifications-changed');
    }

    protected function getNotificationCounts(): array
    {
        $user = auth()->user();

        if (! method_exists($user, 'unreadNotifications')) {
            return [];
        }

        return $user->unreadNotifications
            ->countBy(fn (Notification $notification): ?string => $notification->menuArea())
            ->forget('')
            ->all();
    }

    protected function getChildNotificationCounts(Collection $navigations): array
    {
        $user = auth()->user();

        if (! method_exists($user, 'unreadNotifications')) {
            return [];
        }

        $childRouteNames = $navigations
            ->flatMap(fn (array $navigation): array => data_get($navigation, 'children', []))
            ->pluck('route_name')
            ->filter()
            ->all();

        if (blank($childRouteNames)) {
            return [];
        }

        $counts = [];

        foreach ($user->unreadNotifications as $notification) {
            $route = $notification->menuRoute();

            if (blank($route)) {
                continue;
            }

            if ($match = $this->matchClosestRoute($route, $childRouteNames)) {
                $counts[$match] = ($counts[$match] ?? 0) + 1;
            }
        }

        return $counts;
    }

    protected function matchClosestRoute(string $route, array $routeNames): ?string
    {
        $match = null;

        foreach ($routeNames as $routeName) {
            if ($route !== $routeName && ! str_starts_with($route, $routeName . '.')) {
                continue;
            }

            if (is_null($match) || strlen($routeName) > strlen($match)) {
                $match = $routeName;
            }
        }

        return $match;
    }

    protected function getFavorites(): ?array
    {
        if (! method_exists(auth()->user(), 'favorites')) {
            return null;
        }

        return auth()->user()
            ->favorites()
            ->get(['id', 'name', 'url'])
            ->toArray();
    }

    protected function getMenu(): Collection
    {
        $menuAll = Menu::all();
        data_forget($menuAll, 'settings.children');
        $menuHash = md5(serialize($menuAll) . '|' . $this->authFingerprint());

        $cached = Session::get('navigations.' . $menuHash);
        if (is_array($cached)) {
            return collect($cached);
        }

        $guard = explode('_', Auth::guard()->getName());

        $navigations = Menu::forGuard($guard[1]);
        data_forget($navigations, 'settings.children');

        foreach ($navigations as $group => &$items) {
            if (data_get($items, 'children')) {
                $items['uri'] = find_common_base_uri($items);
                $items['is_virtual_uri'] = true;
            }
        }

        array_walk_recursive($navigations, function (&$item, $key): void {
            if ($key === 'label') {
                $item = __(Str::headline($item));
            }
        });

        if ($this->setting['nav']['append_links'] ?? false) {
            foreach ($this->setting['nav']['append_links'] as $index => $appendLink) {
                $appendLink['uri'] = __($appendLink['uri']);
                $navigations['append' . $index] = $appendLink;
            }
        }

        Session::put('navigations.' . $menuHash, $navigations);

        return collect($navigations);
    }

    protected function authFingerprint(): string
    {
        $user = auth()->user();

        if (! $user) {
            return 'guest';
        }

        $permissionIds = method_exists($user, 'getAllPermissions')
            ? $user->getAllPermissions()->pluck('id')->sort()->implode(',')
            : '';

        return $user->getAuthIdentifier() . ':' . $permissionIds;
    }

    protected function getVisits(): ?array
    {
        if (! method_exists(auth()->user(), 'activities')) {
            return null;
        }

        return auth()->user()
            ->activities()
            ->selectRaw('count(*) as count, description')
            ->where('event', 'visit')
            ->groupBy('description')
            ->orderByDesc('count')
            ->limit(5)
            ->pluck('description')
            ->toArray();
    }
}
