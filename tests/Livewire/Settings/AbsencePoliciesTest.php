<?php

use FluxErp\Livewire\Settings\AbsencePolicies;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AbsencePolicies::class)
        ->assertOk();
});
