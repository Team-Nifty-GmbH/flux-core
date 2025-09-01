<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Contact\Accounting\AllDiscounts;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(AllDiscounts::class)
        ->assertStatus(200);
});
