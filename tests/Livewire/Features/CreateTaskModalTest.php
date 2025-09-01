<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Features\CreateTaskModal;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CreateTaskModal::class)
        ->assertStatus(200);
});
