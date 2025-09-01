<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\Permissions;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Permissions::class)
        ->assertStatus(200);
});
