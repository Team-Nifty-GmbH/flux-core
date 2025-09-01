<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Order\Attachments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Attachments::class, ['orderId' => 1])
        ->assertStatus(200);
});
