<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Contact\Accounting\Discounts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Discounts::class)
        ->assertStatus(200);
});
