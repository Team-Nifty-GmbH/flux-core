<div
    x-data="{
        setParentSearch() {
            $tallstackuiSelect('warehouse-bin-parent-id').mergeRequestParams({
                searchFields: ['code', 'name'],
                where: [
                    ['warehouse_id', '=', $wire.warehouseBin.warehouse_id],
                    ['id', '!=', $wire.warehouseBin.id],
                ],
            });
        },
    }"
>
    <x-modal
        id="edit-warehouse-bin-modal"
        x-on:open="
            if (!$wire.warehouseBin.id)
                $tallstackuiSelect('warehouse-bin-parent-id').clear();
            setParentSearch();
            $tsui.focus('warehouse-bin-code');
        "
        :title="__('Warehouse Bin')"
    >
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                wire:model="warehouseBin.warehouse_id"
                :label="__('Warehouse')"
                required
                select="label:name|value:id"
                x-on:select="setParentSearch()"
                :options="$warehouses"
            />
            <x-input
                id="warehouse-bin-code"
                wire:model="warehouseBin.code"
                :label="__('Code')"
                required
            />
            <x-input wire:model="warehouseBin.name" :label="__('Name')" />
            <x-select.styled
                wire:model="warehouseBin.warehouse_bin_type_enum"
                :label="__('Warehouse Bin Type')"
                required
                select="label:label|value:value"
                :options="\FluxErp\Enums\WarehouseBinTypeEnum::valuesLocalized()"
            />
            <div id="warehouse-bin-parent-id">
                <x-select.styled
                    wire:model="warehouseBin.parent_id"
                    :label="__('Parent')"
                    select="label:label|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\WarehouseBin::class),
                        'method' => 'POST',
                        'params' => [
                            'searchFields' => ['code', 'name'],
                            'where' => [
                                [
                                    'warehouse_id',
                                    '=',
                                    $warehouseBin->warehouse_id,
                                ],
                                ['id', '!=', $warehouseBin->id],
                            ],
                        ],
                    ]"
                />
            </div>
            <x-number
                wire:model.number="warehouseBin.sort_order"
                :label="__('Sort Order')"
            />
            <div class="mt-2 flex flex-col gap-1.5">
                <x-toggle
                    wire:model.boolean="warehouseBin.is_storage_location"
                    :label="__('Is Storage Location')"
                />
                <x-toggle
                    wire:model.boolean="warehouseBin.is_active"
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
                x-on:click="$tsui.close.modal('edit-warehouse-bin-modal')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                x-on:click="
                    $wire.save().then((success) => {
                        if (success)
                            $tsui.close.modal('edit-warehouse-bin-modal');
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
</div>
