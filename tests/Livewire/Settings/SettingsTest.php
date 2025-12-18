<?php

use FluxErp\Livewire\Settings\Settings;
use Livewire\Livewire;

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
    $firstSetting = array_values($settings)[0] ?? null;

    if ($firstSetting) {
        $component->call('showSetting', $firstSetting)
            ->assertSet('settingComponent', $firstSetting['component'])
            ->assertSet('setting', $firstSetting);
    }
});

test('mounts with setting component from url', function (): void {
    $settingsComponent = Livewire::test(Settings::class);
    $settings = $settingsComponent->get('settings');
    $firstSetting = array_values($settings)[0] ?? null;

    if ($firstSetting && isset($firstSetting['component'])) {
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
        expect($setting)->toHaveKey('component');
        expect($setting)->toHaveKey('path');
    }
});
