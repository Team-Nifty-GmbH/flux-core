<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\ProductPropertyGroups;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ProductPropertyGroups::class)
        ->assertStatus(200);
});
