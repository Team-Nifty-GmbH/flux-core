<?php

use FluxErp\Livewire\Portal\Profile;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Profile::class)
        ->assertOk();
});
