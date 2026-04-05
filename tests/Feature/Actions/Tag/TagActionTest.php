<?php

use FluxErp\Actions\Tag\CreateTag;
use FluxErp\Actions\Tag\DeleteTag;
use FluxErp\Actions\Tag\UpdateTag;
use FluxErp\Models\Tag;
use Illuminate\Validation\ValidationException;

test('create tag', function (): void {
    $tag = CreateTag::make([
        'name' => 'VIP',
        'type' => morph_alias(FluxErp\Models\Contact::class),
    ])->validate()->execute();

    expect($tag)->toBeInstanceOf(Tag::class)
        ->name->toBe('VIP');
});

test('create tag requires name', function (): void {
    CreateTag::assertValidationErrors([], 'name');
});

test('create duplicate tag name+type fails', function (): void {
    Tag::factory()->create(['name' => 'VIP', 'type' => 'contact']);

    expect(fn () => CreateTag::make([
        'name' => 'VIP',
        'type' => 'contact',
    ])->validate())->toThrow(ValidationException::class);
});

test('update tag', function (): void {
    $tag = Tag::factory()->create();

    $updated = UpdateTag::make([
        'id' => $tag->getKey(),
        'name' => 'Premium',
    ])->validate()->execute();

    expect($updated->name)->toBe('Premium');
});

test('delete tag', function (): void {
    $tag = Tag::factory()->create();

    expect(DeleteTag::make(['id' => $tag->getKey()])
        ->validate()->execute())->toBeTrue();
});
