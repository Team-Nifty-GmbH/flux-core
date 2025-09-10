<?php

use FluxErp\Livewire\Project\Comments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Comments::class)
        ->assertOk();
});
