<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\RevenueBySalesRepresentative;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RevenueBySalesRepresentative::class)
        ->assertStatus(200);
});
