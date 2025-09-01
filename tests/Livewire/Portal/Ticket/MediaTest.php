<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Portal\Ticket\Media;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Media::class)
        ->assertStatus(200);
});
