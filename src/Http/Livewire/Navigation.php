<?php

namespace FluxErp\Http\Livewire;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Livewire\Component;
use Spatie\Permission\Models\Permission;

class Navigation extends Component
{
    public array $navigations = [];

    public ?string $background;

    public ?array $setting;

    public bool $showSearchBar = true;

    public function boot(): void
    {
        $guard = explode('_', Auth::guard()->getName());

        if (Auth::user()->hasRole('Super Admin')) {
            $query = Permission::query();
        } else {
            $permissionIds = Auth::user()
                ->getAllPermissions()
                ->pluck('id');
            $query = Permission::query()
                ->whereIntegerInRaw('id', $permissionIds);
        }

        $permissions = $query
            ->where('guard_name', $guard[1])
            ->where('name', 'like', '%.get')
            ->whereNot('name', 'like', 'api.%')
            ->whereNot('name', 'like', '%{%}%')
            ->get();

        $navigation['dashboard'] = [
            'label' => 'flux::nav.dashboard.label',
            'icon' => __('flux::nav.dashboard.icon'),
            'uri' => '/',
            'children' => [],
        ];

        foreach ($permissions as $permission) {
            $exploded = explode('.', $permission->name);
            array_pop($exploded);

            $navigation[$exploded[0]]['label'] = 'flux::nav.' . $exploded[0] . '.label';
            $navigation[$exploded[0]]['icon'] = Lang::has('flux::nav.' . $exploded[0] . '.icon') ? __('flux::nav.' . $exploded[0] . '.icon') : null;
            $navigation[$exploded[0]]['uri'] = '/' . $exploded[0];
            $navigation[$exploded[0]]['children'][] = [
                'name' => 'flux::nav.' . implode('.', $exploded) . (count($exploded) === 1 ? '.index' : ''),
                'uri' => '/' . implode('/', $exploded),
            ];
        }

        $this->navigations = $navigation;
    }

    public function mount(array $setting = null, bool $showSearchBar = true): void
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
        return view('flux::livewire.navigation');
    }
}
