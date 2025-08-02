<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Facades\Menu;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Mechanisms\ComponentRegistry;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Traits\HasRoles;

class Settings extends Component
{
    public array $setting = [];

    #[Url(as: 'setting-entry')]
    public ?string $settingComponent = null;

    #[Locked]
    public array $settings = [];

    public function mount(): void
    {
        $this->settings = $this->prepareSettings(data_get(Menu::forGuard('web'), 'settings.children', []));

        if ($this->settingComponent) {
            $this->setting = collect($this->settings)
                ->firstWhere('component', $this->settingComponent);
        }
    }

    public function render(): View
    {
        return view('flux::livewire.settings.settings');
    }

    public function showSetting(array $setting): void
    {
        $this->settingComponent = $setting['component'];
        $this->setting = $setting;
    }

    protected function prepareSettings(array $settings, array $parent = []): array
    {
        foreach ($settings as $key => &$setting) {
            $label = __(Str::headline(data_get($setting, 'label', '')));
            data_set($settings, $key . '.label', $label);
            data_set($settings, $key . '.id', data_get($setting, 'uri', Str::uuid()->toString()));

            $route = Route::getRoutes()
                ->match(Request::create(
                    Str::of(request()->schemeAndHttpHost())->append(parse_url($setting['uri'], PHP_URL_PATH))
                ));
            data_set($settings, $key . '.component', app(ComponentRegistry::class)->getName($route->getAction('controller')));

            $permission = route_to_permission($route);
            if (
                auth()->user()
                && (
                    ! in_array(HasRoles::class, class_uses_recursive(auth()->user()))
                    || ! auth()->user()->hasRole('Super Admin')
                )
            ) {
                try {
                    $hasPermission = auth()->user()?->hasPermissionTo($permission);
                } catch (PermissionDoesNotExist) {
                    $hasPermission = true;
                }

                if ($permission && ! $hasPermission) {
                    unset($settings[$key]);

                    continue;
                }
            }

            if ($parent) {
                data_set($settings, $key . '.path', data_get($parent, 'path') . ' -> ' . $label);
            } else {
                data_set($settings, $key . '.path', $label);
            }

            if ($children = data_get($setting, 'children')) {
                $setting['children'] = $this->prepareSettings($children, $setting);
            }
        }

        return Arr::sort($settings, 'label');
    }
}
