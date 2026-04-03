<?php

use FluxErp\Livewire\Widgets\LostLeadsByLeadLossReason;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(LostLeadsByLeadLossReason::class)
        ->assertOk();
});
