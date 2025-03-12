<?php

namespace FluxErp\Livewire;

use FluxErp\Facades\Menu;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Component;

class Navigation extends Component
{
    public ?string $background;

    public ?array $setting;

    public function mount(?array $setting = null): void
    {
        if ($setting) {
            $setting = $setting['settings'];
            $this->setting = $setting;

            $this->background = ($setting['nav']['background'] ?? false)
                ? 'background: linear-gradient(' . ($setting['nav']['background']['angle'] ?? 0) . 'deg, '
                . ($setting['nav']['background']['start'] ?? 0) . ', '
                . ($setting['nav']['background']['end'] ?? 0) . ');'
                : null;
        }
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.navigation', [
            'navigations' => $this->getMenu(),
            'visits' => $this->getVisits(),
            'favorites' => $this->getFavorites(),
        ]);
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
        $menuHash = md5(serialize($menuAll));

        if (Session::has('navigations.' . $menuHash)) {
            return Session::get('navigations.' . $menuHash);
        }

        $guard = explode('_', Auth::guard()->getName());

        $navigations = Menu::forGuard($guard[1], $guard[1] === 'address' ? 'portal' : null);
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

        Session::put('navigations.' . $menuHash, collect($navigations));

        return collect($navigations);
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
