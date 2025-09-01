<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\ProductOptionGroups;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductOptionGroups::class)
        ->assertStatus(200);
});
