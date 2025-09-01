<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Contact\Accounting\DiscountGroups;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(DiscountGroups::class)
        ->assertStatus(200);
});
