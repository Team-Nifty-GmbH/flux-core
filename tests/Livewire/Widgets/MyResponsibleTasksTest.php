<?php

use FluxErp\Livewire\Widgets\MyResponsibleTasks;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::actingAs($this->user)
        ->test(MyResponsibleTasks::class)
        ->assertOk();
});
