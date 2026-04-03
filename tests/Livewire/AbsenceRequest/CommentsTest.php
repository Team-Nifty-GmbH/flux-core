<?php

use FluxErp\Livewire\AbsenceRequest\Comments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Comments::class)
        ->assertOk();
});
