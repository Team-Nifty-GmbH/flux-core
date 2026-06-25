<?php

use FluxErp\Livewire\Resource\ResourceView;
use FluxErp\Models\Resource;
use Livewire\Livewire;

test('resource view renders', function (): void {
    $resource = Resource::factory()->create();

    Livewire::test(ResourceView::class, ['id' => $resource->getKey()])
        ->assertOk();
});
