<?php

use FluxErp\Livewire\Portal\Files;
use Livewire\Livewire;

test('renders successfully', function (): void {
    $this->be($this->address, 'address');

    Livewire::test(Files::class)
        ->assertOk();
});
