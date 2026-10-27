<?php

const BRIDGE_CONFIG_WIRE = "Livewire.all().find((c) => c.name === 'settings.printers').\$wire";
const MERCURE_TOGGLE = '#printer-bridge-config-modal input[wire\:model\.live\.boolean="configForm.mercure_enabled"]';
const REVERB_TOGGLE = '#printer-bridge-config-modal input[wire\:model\.boolean="configForm.reverb_disabled"]';

test('turning mercure off in the bridge config modal brings reverb back', function (): void {
    config([
        'broadcasting.default' => 'mercure',
        'broadcasting.connections.reverb.app_id' => 'reverb-id',
        'broadcasting.connections.reverb.secret' => 'reverb-secret',
    ]);

    $page = visit(route('settings.printers'))->assertNoSmoke();

    $page->script("() => \$tsui.open.modal('printer-bridge-config-modal')");
    waitForCondition($page, "() => document.getElementById('printer-bridge-config-modal')?.offsetParent !== null");

    $page->assertChecked(MERCURE_TOGGLE)
        ->assertChecked(REVERB_TOGGLE)
        ->click(MERCURE_TOGGLE);

    waitForCondition($page, '() => ' . BRIDGE_CONFIG_WIRE . '.configForm.reverb_disabled === false');

    $page->assertNotChecked(REVERB_TOGGLE)
        ->click('#printer-bridge-config-modal button:has-text("Generate Configuration")');

    waitForCondition($page, '() => !! ' . BRIDGE_CONFIG_WIRE . '.configForm.bridge_config?.instance_name');

    $page->assertScript(BRIDGE_CONFIG_WIRE . '.configForm.bridge_config.mercure_enabled', false)
        ->assertScript(BRIDGE_CONFIG_WIRE . '.configForm.bridge_config.reverb_disabled', false)
        ->assertScript(BRIDGE_CONFIG_WIRE . '.configForm.bridge_config.reverb_app_secret', 'reverb-secret')
        ->assertNoSmoke();
});

test('the bridge config modal hides the mercure toggle on a reverb instance', function (): void {
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.app_id' => 'reverb-id',
    ]);

    $page = visit(route('settings.printers'))->assertNoSmoke();

    $page->script("() => \$tsui.open.modal('printer-bridge-config-modal')");
    waitForCondition($page, "() => document.getElementById('printer-bridge-config-modal')?.offsetParent !== null");

    $page->assertPresent(REVERB_TOGGLE)
        ->assertNotPresent(MERCURE_TOGGLE)
        ->assertNotChecked(REVERB_TOGGLE)
        ->assertNoSmoke();
});
