<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Portal\Shop\CheckoutFinish;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CheckoutFinish::class)
        ->assertStatus(200);
});
