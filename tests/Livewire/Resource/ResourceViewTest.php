<?php

use FluxErp\Livewire\Resource\ResourceView;
use FluxErp\Models\Resource;
use Livewire\Livewire;

test('resource view renders', function (): void {
    $resource = Resource::factory()->create();

    Livewire::test(ResourceView::class, ['id' => $resource->getKey()])
        ->assertOk();
});

test('startEdit sets edit to true', function (): void {
    $resource = Resource::factory()->create();

    Livewire::test(ResourceView::class, ['id' => $resource->getKey()])
        ->call('startEdit')
        ->assertSet('edit', true)
        ->assertOk();
});

test('cancel resets edit to false without TypeError', function (): void {
    $resource = Resource::factory()->create();

    Livewire::test(ResourceView::class, ['id' => $resource->getKey()])
        ->set('edit', true)
        ->call('cancel')
        ->assertSet('edit', false)
        ->assertOk();
});
