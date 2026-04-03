<?php

use FluxErp\Livewire\Widgets\ConversionRateByLeadOrigin;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(ConversionRateByLeadOrigin::class)
        ->assertOk();
});
