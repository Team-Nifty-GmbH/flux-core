<?php

use FluxErp\Livewire\Accounting\Transactions\Comments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Comments::class)
        ->assertOk();
});
