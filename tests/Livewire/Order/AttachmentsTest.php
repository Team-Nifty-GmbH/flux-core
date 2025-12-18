<?php

use FluxErp\Livewire\Order\Attachments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Attachments::class, ['orderId' => 1])
        ->assertOk();
});
