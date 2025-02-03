<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Facades\Menu;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Traits\HasRoles;

class Settings extends Component
{
    public array $settings = [];

    public array $setting = [];

    public function mount(): void
    {
        $this->settings = $this->prepareSettings(data_get(Menu::forGuard('web'), 'settings.children', []));
    }

    public function render(): View
    {
        return view('flux::livewire.settings.settings');
    }

    public function showSetting(array $setting): void
    {
        $route = Route::getRoutes()->match(Request::create(parse_url($setting['uri'], PHP_URL_PATH)));
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
                throw UnauthorizedException::forPermissions([$permission]);
            }
        }

        $setting['component'] = $route->getAction('controller');

        $this->setting = $setting;
    }

    protected function prepareSettings(array $settings, array $parent = []): array
    {
        foreach ($settings as $key => &$setting) {
            $label = __(Str::headline(data_get($setting, 'label', '')));
            data_set($settings, $key . '.label', $label);
            data_set($settings, $key . '.id', data_get($setting, 'uri', Str::uuid()->toString()));

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
