<div
    x-data="{
        setBinSearch() {
            $tallstackuiSelect(
                'stock-posting-warehouse-bin-id',
            ).mergeRequestParams({
                where: [
                    ['warehouse_id', '=', $wire.stockPosting.warehouse_id],
                    ['is_storage_location', '=', true],
                    ['is_active', '=', true],
                ],
            });
        },
    }"
>
    <x-modal
        id="create-stock-posting-modal"
        x-on:open="
            $tallstackuiSelect('stock-posting-warehouse-bin-id').clear();
            setBinSearch();
        "
    >
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                wire:model="stockPosting.warehouse_id"
                :label="__('Warehouse')"
                required
                select="label:name|value:id"
                x-on:select="setBinSearch()"
                :options="$warehouses"
            />
            <div id="stock-posting-warehouse-bin-id">
                <x-select.styled
                    wire:model="stockPosting.warehouse_bin_id"
                    :label="__('Warehouse Bin')"
                    select="label:label|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\WarehouseBin::class),
                        'method' => 'POST',
                        'params' => [
                            'where' => [
                                [
                                    'warehouse_id',
                                    '=',
                                    $stockPosting->warehouse_id,
                                ],
                                ['is_storage_location', '=', true],
                                ['is_active', '=', true],
                            ],
                        ],
                    ]"
                />
            </div>
            <x-select.styled
                wire:model="stockPosting.lot_id"
                :label="__('Lot')"
                select="label:lot_number|value:id"
                :options="$lots"
            />
            <x-number
                wire:model="stockPosting.posting"
                :label="__('Posting')"
            />
            <x-number
                wire:model="stockPosting.purchase_price"
                :label="__('Purchase Price')"
            />
            <x-textarea
                wire:model="stockPosting.description"
                :label="__('Description')"
            />
            @if ($hasSerialNumbers)
                <hr />
                <x-select.styled
                    wire:model="stockPosting.serial_number.serial_number_range_id"
                    :label="__('Serial Number Range')"
                    select="label:type|value:id"
                    :options="$serialNumberRanges"
                />
                <x-input
                    wire:model="stockPosting.serial_number.serial_number"
                    :label="__('Serial Number')"
                />
                <x-input
                    wire:model="stockPosting.serial_number.supplier_serial_number"
                    :label="__('Supplier Serial Number')"
                />
                <x-toggle
                    wire:model="stockPosting.serial_number.use_supplier_serial_number"
                    :label="__('Use Supplier Serial Number')"
                />
            @endif
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$tsui.close.modal('create-stock-posting-modal')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                x-on:click="
                    $wire.save().then((success) => {
                        if (success)
                            $tsui.close.modal('create-stock-posting-modal');
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
</div>
