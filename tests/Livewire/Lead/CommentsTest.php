<?php

use FluxErp\Livewire\Lead\Comments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Comments::class)
        ->assertOk();
});
