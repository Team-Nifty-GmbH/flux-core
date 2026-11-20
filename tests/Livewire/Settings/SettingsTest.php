<?php

use FluxErp\Livewire\Settings\Settings;
use FluxErp\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

function findFirstSettingWithComponent(array $settings): ?array
{
    foreach ($settings as $setting) {
        if (isset($setting['component'])) {
            return $setting;
        }

        if (! empty($setting['children'])) {
            $found = findFirstSettingWithComponent($setting['children']);
            if ($found) {
                return $found;
            }
        }
    }

    return null;
}

test('renders successfully', function (): void {
    Livewire::test(Settings::class)
        ->assertOk();
});

test('settings are loaded on mount', function (): void {
    Livewire::test(Settings::class)
        ->assertSet('settings', fn ($settings) => is_array($settings) && count($settings) > 0);
});

test('can show setting', function (): void {
    $component = Livewire::test(Settings::class);

    $settings = $component->get('settings');
    $firstSetting = findFirstSettingWithComponent($settings);

    if ($firstSetting) {
        $component->call('showSetting', $firstSetting)
            ->assertSet('settingComponent', $firstSetting['component'])
            ->assertSet('setting', $firstSetting);
    }
});

test('mounts with setting component from url', function (): void {
    $settingsComponent = Livewire::test(Settings::class);
    $settings = $settingsComponent->get('settings');
    $firstSetting = findFirstSettingWithComponent($settings);

    if ($firstSetting) {
        Livewire::withQueryParams(['setting-entry' => $firstSetting['component']])
            ->test(Settings::class)
            ->assertSet('settingComponent', $firstSetting['component'])
            ->assertSet('setting.component', $firstSetting['component']);
    }
});

test('settings have required structure', function (): void {
    $component = Livewire::test(Settings::class);
    $settings = $component->get('settings');

    foreach ($settings as $setting) {
        expect($setting)->toHaveKey('label');
        expect($setting)->toHaveKey('id');
        expect($setting)->toHaveKey('path');

        // Settings have either a component (leaf) or children (group)
        $hasComponent = isset($setting['component']);
        $hasChildren = isset($setting['children']);
        expect($hasComponent || $hasChildren)->toBeTrue();
    }
});

test('showSetting discards a component that is not a settings entry', function (): void {
    Livewire::test(Settings::class)
        ->call('showSetting', ['component' => 'dashboard.dashboard'])
        ->assertOk()
        ->assertDontSeeLivewire('dashboard.dashboard')
        ->assertSet('settingComponent', null)
        ->assertSet('setting', []);
});

test('showSetting discards an entry the user has no permission for', function (): void {
    $setting = findFirstSettingWithComponent(Livewire::test(Settings::class)->get('settings'));
    Permission::findOrCreate(
        route_to_permission(Route::getRoutes()->match(Request::create(data_get($setting, 'uri'))), false),
        'web'
    );
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    Livewire::test(Settings::class)
        ->call('showSetting', $setting)
        ->assertOk()
        ->assertDontSeeLivewire($setting['component'])
        ->assertSet('settingComponent', null);
});

test('showSetting takes the entry from the server, not from the client', function (): void {
    $setting = findFirstSettingWithComponent(Livewire::test(Settings::class)->get('settings'));

    Livewire::test(Settings::class)
        ->call('showSetting', array_merge($setting, ['path' => 'forged']))
        ->assertSet('settingComponent', $setting['component'])
        ->assertSet('setting', $setting);
});

test('settingComponent cannot be set from the client', function (): void {
    Livewire::test(Settings::class)
        ->set('settingComponent', 'dashboard.dashboard');
})->throws(CannotUpdateLockedPropertyException::class);
