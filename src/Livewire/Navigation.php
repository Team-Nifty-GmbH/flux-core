<?php

namespace FluxErp\Livewire;

use FluxErp\Facades\Menu;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Navigation extends Component
{
    public ?string $background;

    public ?array $setting;

    public bool $showSearchBar = true;

    public function mount(?array $setting = null, bool $showSearchBar = true): void
    {
        $this->showSearchBar = $showSearchBar;

        if ($setting) {
            $setting = $setting['settings'];
            $this->setting = $setting;

            $this->background = ($setting['nav']['background'] ?? false)
                ? 'background: linear-gradient(' . ($setting['nav']['background']['angle'] ?? 0) . 'deg, ' . ($setting['nav']['background']['start'] ?? 0) . ', ' . ($setting['nav']['background']['end'] ?? 0) . ');'
                : null;

            if ($this->setting['nav']['append_links'] ?? false) {
                foreach ($this->setting['nav']['append_links'] as $index => $appendLink) {
                    $appendLink['uri'] = __($appendLink['uri']);
                    $this->navigations['append' . $index] = $appendLink;
                }
            }
        }
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.navigation', [
            'visits' => $this->getVisits(),
            'navigations' => $this->getMenu(),
            'favorites' => $this->getFavorites(),
        ]);
    }

    public function addFavorite(string $url, ?string $name = null): void
    {
        auth()->user()->favorites()->create([
            'name' => $name ?: $url,
            'url' => $url,
        ]);
    }

    public function deleteFavorite(int $id): void
    {
        auth()->user()
            ->favorites()
            ->whereKey($id)
            ->delete();
    }

    protected function getVisits(): ?array
    {
        if (method_exists(auth()->user(), 'activities')) {
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

    protected function getMenu(): Collection
    {
        $guard = explode('_', Auth::guard()->getName());

        $navigations = Menu::forGuard($guard[1], $guard[1] === 'address' ? 'portal' : null);
        array_walk_recursive($navigations, function (&$item, $key) {
            if ($key === 'label') {
                $item = __(Str::headline($item));
            }
        });

        return collect($navigations);
    }

    protected function getFavorites(): ?array
    {
        if (method_exists(auth()->user(), 'favorites')) {
            return null;
        }

        return auth()->user()
            ->favorites()
            ->select(['id', 'name', 'url'])
            ->get()
            ->toArray();
    }
}
