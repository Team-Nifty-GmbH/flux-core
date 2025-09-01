<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Product\BundleList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(BundleList::class)
        ->assertStatus(200);
});
