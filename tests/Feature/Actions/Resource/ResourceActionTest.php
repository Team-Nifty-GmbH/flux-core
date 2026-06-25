<?php

use FluxErp\Models\Resource;

test('resource factory creates a resource', function (): void {
    $resource = Resource::factory()->create(['name' => 'VW Bus W-AB 123']);

    expect($resource)->toBeInstanceOf(Resource::class)
        ->name->toBe('VW Bus W-AB 123')
        ->and($resource->uuid)->not->toBeNull();
});
