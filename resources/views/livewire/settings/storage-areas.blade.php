<div
    x-data="{
        setParentSearch() {
            $tallstackuiSelect('storage-area-parent-id').mergeRequestParams({
                searchFields: ['code', 'name'],
                where: [
                    ['warehouse_id', '=', $wire.storageArea.warehouse_id],
                    ['id', '!=', $wire.storageArea.id],
                ],
            });
        },
    }"
>
    <x-modal
        id="edit-storage-area-modal"
        x-on:open="
            if (!$wire.storageArea.id)
                $tallstackuiSelect('storage-area-parent-id').clear();
            setParentSearch();
            $tsui.focus('storage-area-code');
        "
        :title="__('Storage Area')"
    >
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                wire:model="storageArea.warehouse_id"
                :label="__('Warehouse')"
                required
                select="label:name|value:id"
                x-on:select="setParentSearch()"
                :options="$warehouses"
            />
            <x-input
                id="storage-area-code"
                wire:model="storageArea.code"
                :label="__('Code')"
                required
            />
            <x-input wire:model="storageArea.name" :label="__('Name')" />
            <x-select.styled
                wire:model="storageArea.storage_area_type_enum"
                :label="__('Storage Area Type')"
                required
                select="label:label|value:value"
                :options="\FluxErp\Enums\StorageAreaTypeEnum::valuesLocalized()"
            />
            <div id="storage-area-parent-id">
                <x-flux::warehouse.storage-area-select
                    model="storageArea.parent_id"
                    :label="__('Parent')"
                    :warehouse-id="$storageArea->warehouse_id"
                    :exclude-id="$storageArea->id"
                />
            </div>
            <x-number
                wire:model.number="storageArea.sort_number"
                :label="__('Sort Number')"
            />
            <div class="mt-2 flex flex-col gap-1.5">
                <x-toggle
                    wire:model.boolean="storageArea.is_storage_location"
                    :label="__('Is Storage Location')"
                />
                <x-toggle
                    wire:model.boolean="storageArea.is_active"
                    :label="__('Active')"
                />
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$tsui.close.modal('edit-storage-area-modal')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                x-on:click="
                    $wire.save().then((success) => {
                        if (success)
                            $tsui.close.modal('edit-storage-area-modal');
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
</div>
