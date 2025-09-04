<?php

use FluxErp\Livewire\SignaturePublicLink\Comments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Comments::class)
        ->assertOk();
});
