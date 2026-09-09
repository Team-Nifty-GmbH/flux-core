<?php

use function Livewire\invade;

test('the permission reset for long running workers is switched on while registering', function (): void {
    config(['permission.register_octane_reset_listener' => false]);

    $provider = new FluxErp\FluxServiceProvider($this->app);
    invade($provider)->registerConfig();

    expect(config('permission.register_octane_reset_listener'))->toBeTrue();
});
