<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Address\Attachments;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Attachments::class)
        ->assertStatus(200);
});
