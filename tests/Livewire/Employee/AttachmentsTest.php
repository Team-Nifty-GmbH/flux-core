<?php

use FluxErp\Livewire\Employee\Attachments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Attachments::class)
        ->assertOk();
});
