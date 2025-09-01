<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Settings\System\Session;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Session::class)
        ->assertStatus(200);
});
