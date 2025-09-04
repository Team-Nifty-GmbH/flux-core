<?php

use FluxErp\Livewire\DataTables\MediaList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(MediaList::class)
        ->assertStatus(200);
});
