<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Portal\Auth\Logout;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Logout::class)
        ->assertStatus(200);
});
