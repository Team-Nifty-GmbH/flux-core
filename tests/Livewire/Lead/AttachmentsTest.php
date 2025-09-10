<?php

use FluxErp\Livewire\Lead\Attachments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Attachments::class)
        ->assertOk();
});
