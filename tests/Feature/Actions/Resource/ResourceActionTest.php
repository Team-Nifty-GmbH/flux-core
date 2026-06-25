<?php

use FluxErp\Actions\Resource\CreateResource;
use FluxErp\Actions\Resource\DeleteResource;
use FluxErp\Actions\Resource\UpdateResource;
use FluxErp\Models\Resource;

test('resource factory creates a resource', function (): void {
    $resource = Resource::factory()->create(['name' => 'VW Bus W-AB 123']);

    expect($resource)->toBeInstanceOf(Resource::class)
        ->name->toBe('VW Bus W-AB 123')
        ->and($resource->uuid)->not->toBeNull();
});

test('create resource', function (): void {
    $resource = CreateResource::make([
        'name' => 'Room 101',
        'resource_number' => 'R-101',
    ])->validate()->execute();

    expect($resource)->toBeInstanceOf(Resource::class)
        ->name->toBe('Room 101');
});

test('create resource requires name', function (): void {
    CreateResource::assertValidationErrors([], ['name']);
});

test('update resource', function (): void {
    $resource = Resource::factory()->create(['name' => 'Old']);

    $updated = UpdateResource::make([
        'id' => $resource->getKey(),
        'name' => 'New',
    ])->validate()->execute();

    expect($updated->name)->toBe('New');
});

test('delete resource soft deletes', function (): void {
    $resource = Resource::factory()->create();

    DeleteResource::make(['id' => $resource->getKey()])->validate()->execute();

    expect(Resource::query()->whereKey($resource->getKey())->exists())->toBeFalse()
        ->and(Resource::withTrashed()->whereKey($resource->getKey())->exists())->toBeTrue();
});
