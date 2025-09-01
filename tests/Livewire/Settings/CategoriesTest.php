<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Settings\Categories;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Categories::class)
        ->assertStatus(200);
});
