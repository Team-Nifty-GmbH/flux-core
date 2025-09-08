<?php

use FluxErp\Livewire\Address\Attachments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Attachments::class)
        ->assertOk();
});
