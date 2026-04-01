<?php

use FluxErp\Livewire\EmployeeDay\Comments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Comments::class)
        ->assertOk();
});
