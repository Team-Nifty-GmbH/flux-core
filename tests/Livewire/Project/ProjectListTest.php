<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Project\ProjectList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProjectList::class)
        ->assertStatus(200);
});
