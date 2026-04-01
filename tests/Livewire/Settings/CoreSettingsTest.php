<?php

use FluxErp\Livewire\Settings\CoreSettings;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CoreSettings::class)
        ->assertOk();
});
