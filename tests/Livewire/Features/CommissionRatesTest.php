<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use FluxErp\Livewire\Features\CommissionRates;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CommissionRates::class, ['userId' => $this->user->id])
        ->assertStatus(200);
});
