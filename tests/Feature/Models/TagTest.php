<?php

use FluxErp\Models\Lead;
use FluxErp\Models\Tag;

test('findOrCreate stores a plain string name and slug', function (): void {
    $tag = Tag::findOrCreate('Wichtiger Kunde');

    expect($tag)->toBeInstanceOf(Tag::class)
        ->and($tag->getRawOriginal('name'))->toBe('Wichtiger Kunde')
        ->and($tag->slug)->toBe('wichtiger-kunde');
});

test('findOrCreate reuses an existing tag instead of duplicating it', function (): void {
    Tag::findOrCreate('Wichtiger Kunde');
    Tag::findOrCreate('Wichtiger Kunde');

    expect(Tag::query()->where('name', 'Wichtiger Kunde')->count())->toBe(1);
});

test('findOrCreate resolves a list of names to plain string tags', function (): void {
    $tags = Tag::findOrCreate(['VIP', 'Lead']);

    expect($tags)->toHaveCount(2)
        ->and($tags->pluck('name')->all())->toEqualCanonicalizing(['VIP', 'Lead']);
});

test('attachTag on a model stores a plain string tag name', function (): void {
    $lead = Lead::factory()->create();

    $lead->attachTag('Wichtiger Kunde');

    expect($lead->tags()->pluck('name')->all())->toBe(['Wichtiger Kunde']);
});
