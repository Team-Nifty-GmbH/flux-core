<?php

use FluxErp\Actions\MediaFolder\CreateMediaFolder;
use FluxErp\Actions\MediaFolder\DeleteMediaFolder;
use FluxErp\Actions\MediaFolder\UpdateMediaFolder;

test('create media folder', function (): void {
    $folder = CreateMediaFolder::make([
        'name' => 'Documents',
        'model_type' => morph_alias(FluxErp\Models\Contact::class),
        'model_id' => FluxErp\Models\Contact::factory()->create()->getKey(),
    ])->validate()->execute();

    expect($folder)->name->toBe('Documents');
});

test('create media folder requires name model_type model_id', function (): void {
    CreateMediaFolder::assertValidationErrors([], ['name', 'model_type', 'model_id']);
});

test('update media folder', function (): void {
    $folder = CreateMediaFolder::make([
        'name' => 'Original',
        'model_type' => morph_alias(FluxErp\Models\Contact::class),
        'model_id' => FluxErp\Models\Contact::factory()->create()->getKey(),
    ])->validate()->execute();

    $updated = UpdateMediaFolder::make([
        'id' => $folder->getKey(),
        'name' => 'Invoices',
    ])->validate()->execute();

    expect($updated->name)->toBe('Invoices');
});

test('delete media folder', function (): void {
    $folder = CreateMediaFolder::make([
        'name' => 'Temp',
        'model_type' => morph_alias(FluxErp\Models\Contact::class),
        'model_id' => FluxErp\Models\Contact::factory()->create()->getKey(),
    ])->validate()->execute();

    expect(DeleteMediaFolder::make(['id' => $folder->getKey()])
        ->validate()->execute())->toBeTrue();
});

test('renaming media folder with apostrophe in name does not break descendant slug update', function (): void {
    $contact = FluxErp\Models\Contact::factory()->create();

    $parent = CreateMediaFolder::make([
        'name' => "Zusatzinfo's",
        'model_type' => morph_alias(FluxErp\Models\Contact::class),
        'model_id' => $contact->getKey(),
    ])->validate()->execute();

    $child = CreateMediaFolder::make([
        'parent_id' => $parent->getKey(),
        'name' => 'Sub',
        'model_type' => morph_alias(FluxErp\Models\Contact::class),
        'model_id' => $contact->getKey(),
    ])->validate()->execute();

    UpdateMediaFolder::make([
        'id' => $parent->getKey(),
        'name' => "Zusatzinfo's renamed",
    ])->validate()->execute();

    expect($child->fresh()->slug)
        ->toBe("zusatzinfo's_renamed|{$parent->getKey()}.sub|{$child->getKey()}");
});
