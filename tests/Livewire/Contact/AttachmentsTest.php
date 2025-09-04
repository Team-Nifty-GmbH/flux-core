<?php

use FluxErp\Livewire\Contact\Attachments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Attachments::class)
        ->assertOk();
});
