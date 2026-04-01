<?php

use FluxErp\Livewire\Widgets\MyOverdueTasks;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MyOverdueTasks::class)
        ->assertOk();
});
