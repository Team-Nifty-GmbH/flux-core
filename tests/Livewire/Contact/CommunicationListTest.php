<?php

uses(FluxErp\Tests\TestCase::class);
use FluxErp\Livewire\Contact\CommunicationList;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CommunicationList::class)
        ->assertStatus(200);
});
