<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Project\TasksByState;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(TasksByState::class)
        ->assertStatus(200);
});
