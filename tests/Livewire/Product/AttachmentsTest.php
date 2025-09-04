<?php

use FluxErp\Livewire\Product\Attachments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Attachments::class)
        ->assertOk();
});
