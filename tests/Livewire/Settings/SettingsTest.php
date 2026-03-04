<?php

use FluxErp\Livewire\Settings\Settings;
use Livewire\Livewire;

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
