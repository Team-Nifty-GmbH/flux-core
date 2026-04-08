<?php

use FluxErp\Livewire\DataTables\CommunicationList;
use FluxErp\Models\Communication;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(CommunicationList::class)
        ->assertOk();
});

test('renders with communication records without type error', function (): void {
    Communication::factory()->create([
        'communication_type_enum' => 'mail',
        'from' => 'john@example.com',
        'to' => ['jane@example.com'],
    ]);

    Livewire::test(CommunicationList::class)
        ->assertOk();
});

test('augmentItemArray translates communication type enum', function (): void {
    $communication = Communication::factory()->create([
        'communication_type_enum' => 'mail',
        'from' => 'john@example.com',
        'to' => ['jane@example.com'],
    ]);

    $component = new CommunicationList();
    $method = new ReflectionMethod($component, 'augmentItemArray');

    $itemArray = ['communication_type_enum' => 'mail', 'text_body' => str_repeat('a', 200)];
    $method->invokeArgs($component, [&$itemArray, $communication]);

    expect($itemArray['communication_type_enum'])->toBe(__('Mail'))
        ->and(strlen($itemArray['text_body']))->toBeLessThanOrEqual(103);
});
