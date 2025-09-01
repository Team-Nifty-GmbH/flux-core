<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Order\Projects;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Projects::class)
        ->assertStatus(200);
});
