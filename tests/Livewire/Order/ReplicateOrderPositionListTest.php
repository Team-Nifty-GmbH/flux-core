<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Order\ReplicateOrderPositionList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ReplicateOrderPositionList::class)
        ->assertStatus(200);
});
