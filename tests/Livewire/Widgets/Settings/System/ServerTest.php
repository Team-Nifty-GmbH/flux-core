<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Settings\System\Server;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Server::class)
        ->assertStatus(200);
});
