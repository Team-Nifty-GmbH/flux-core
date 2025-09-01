<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Task\Comments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Comments::class)
        ->assertStatus(200);
});
