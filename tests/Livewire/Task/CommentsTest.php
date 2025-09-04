<?php

use FluxErp\Livewire\Task\Comments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Comments::class)
        ->assertOk();
});
