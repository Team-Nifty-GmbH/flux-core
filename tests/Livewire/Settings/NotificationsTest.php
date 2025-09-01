<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Settings\Notifications;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Notifications::class)
        ->assertStatus(200);
});
