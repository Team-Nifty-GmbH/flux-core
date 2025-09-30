<?php

use FluxErp\Livewire\RecordMerging;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RecordMerging::class)
        ->assertOk();
});
