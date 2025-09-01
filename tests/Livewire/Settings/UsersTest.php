<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Settings\Users;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Users::class)
        ->assertStatus(200);
});
