<?php

use FluxErp\Livewire\Settings\Tags;
use FluxErp\Models\Tag;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(Tags::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(Tags::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('tagForm.id', null)
        ->assertSet('tagForm.name', null)
        ->assertSet('tagForm.color', null)
        ->assertSet('tagForm.type', null)
        ->assertOpensModal('edit-tag-modal');
});

test('edit with model fills form and opens modal', function (): void {
    $tag = Tag::factory()->create();

    Livewire::test(Tags::class)
        ->call('edit', $tag->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('tagForm.id', $tag->getKey())
        ->assertSet('tagForm.name', $tag->name)
        ->assertOpensModal('edit-tag-modal');
});

test('can create tag', function (): void {
    Livewire::test(Tags::class)
        ->assertOk()
        ->call('edit')
        ->set('tagForm.name', $name = Str::uuid()->toString())
        ->set('tagForm.type', 'test_type')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseHas('tags', [
        'name' => $name,
        'type' => 'test_type',
    ]);
});

test('can update tag', function (): void {
    $tag = Tag::factory()->create();

    Livewire::test(Tags::class)
        ->assertOk()
        ->call('edit', $tag->getKey())
        ->assertSet('tagForm.id', $tag->getKey())
        ->set('tagForm.name', 'Updated Tag Name')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($tag->refresh()->name)->toEqual('Updated Tag Name');
});

test('can delete tag', function (): void {
    $tag = Tag::factory()->create();

    Livewire::test(Tags::class)
        ->assertOk()
        ->call('delete', $tag->getKey())
        ->assertOk()
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('tags', [
        'id' => $tag->getKey(),
    ]);
});
