<?php

namespace FluxErp\Livewire;

use FluxErp\Facades\Menu;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class Navigation extends Component
{
    public array $navigations = [];

    public ?string $background;

    public ?array $setting;

    public bool $showSearchBar = true;

    public function mount(array $setting = null, bool $showSearchBar = true): void
    {
        $guard = explode('_', Auth::guard()->getName());

        $this->navigations = Menu::forGuard($guard[1], $guard[1] === 'address' ? 'portal' : null);
        array_walk_recursive($this->navigations, function (&$item, $key) {
            if ($key === 'label') {
                $item = __(Str::headline($item));
            }
        });

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
        return view('flux::livewire.navigation');
    }
}
