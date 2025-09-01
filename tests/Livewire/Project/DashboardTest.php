<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Project\Dashboard;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Dashboard::class)
        ->assertStatus(200);
});
