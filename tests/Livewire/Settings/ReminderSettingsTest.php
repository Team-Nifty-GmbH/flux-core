<?php

use FluxErp\Livewire\Settings\ReminderSettings;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ReminderSettings::class)
        ->assertOk();
});
