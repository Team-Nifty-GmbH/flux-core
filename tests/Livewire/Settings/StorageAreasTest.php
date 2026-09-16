<?php

use FluxErp\Enums\StorageAreaTypeEnum;
use FluxErp\Livewire\Settings\StorageAreas;
use FluxErp\Models\Warehouse;
use FluxErp\Models\StorageArea;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->warehouse = Warehouse::factory()->create();
});

test('renders successfully', function (): void {
    Livewire::test(StorageAreas::class)
        ->assertOk();
});

test('edit with null resets form and opens modal', function (): void {
    Livewire::test(StorageAreas::class)
        ->call('edit')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('storageArea.id', null)
        ->assertSet('storageArea.code', null)
        ->assertSet('storageArea.parent_id', null)
        ->assertSet('storageArea.is_active', true)
        ->assertSet('storageArea.is_storage_location', false)
        ->assertOpensModal('edit-storage-area-modal');
});

test('edit with model fills form', function (): void {
    $storageArea = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
    ]);

    Livewire::test(StorageAreas::class)
        ->call('edit', $storageArea->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSet('storageArea.id', $storageArea->getKey())
        ->assertSet('storageArea.code', $storageArea->code)
        ->assertSet('storageArea.warehouse_id', $this->warehouse->getKey());
});

test('can create warehouse bin', function (): void {
    $code = Str::uuid()->toString();

    Livewire::test(StorageAreas::class)
        ->call('edit')
        ->set('storageArea.warehouse_id', $this->warehouse->getKey())
        ->set('storageArea.code', $code)
        ->set('storageArea.storage_area_type_enum', StorageAreaTypeEnum::Container->value)
        ->set('storageArea.is_storage_location', true)
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertDatabaseHas('storage_areas', [
        'code' => $code,
        'warehouse_id' => $this->warehouse->getKey(),
        'is_storage_location' => true,
    ]);
});

test('can update warehouse bin', function (): void {
    $storageArea = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
    ]);

    Livewire::test(StorageAreas::class)
        ->call('edit', $storageArea->getKey())
        ->set('storageArea.name', 'Updated Bin Name')
        ->call('save')
        ->assertOk()
        ->assertHasNoErrors();

    expect($storageArea->refresh()->name)->toEqual('Updated Bin Name');
});

test('can delete warehouse bin', function (): void {
    $storageArea = StorageArea::factory()->create([
        'warehouse_id' => $this->warehouse->getKey(),
        'storage_area_type_enum' => StorageAreaTypeEnum::Container,
    ]);

    Livewire::test(StorageAreas::class)
        ->call('delete', $storageArea->getKey())
        ->assertOk()
        ->assertHasNoErrors()
        ->assertReturned(true);

    $this->assertSoftDeleted('storage_areas', ['id' => $storageArea->getKey()]);
});

test('save fails without required fields', function (): void {
    Livewire::test(StorageAreas::class)
        ->call('edit')
        ->set('storageArea.code', null)
        ->call('save')
        ->assertOk()
        ->assertHasErrors(['storageArea.code'])
        ->assertReturned(false);
});

test('the parent select sends search fields so the search endpoint accepts it', function (): void {
    $html = Livewire::test(StorageAreas::class)
        ->assertOk()
        ->html();

    expect($html)->toContain('storage-area-parent-id')
        ->and(html_entity_decode($html))->toContain('searchFields');
});
