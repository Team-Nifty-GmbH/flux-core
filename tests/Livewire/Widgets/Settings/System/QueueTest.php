<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Widgets\Settings\System\Queue;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Queue::class)
        ->assertStatus(200);
});
