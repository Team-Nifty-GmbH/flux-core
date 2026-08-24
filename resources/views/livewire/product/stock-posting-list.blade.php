<div
    x-data="{
        setBinSearch() {
            $tallstackuiSelect(
                'stock-posting-warehouse-bin-id',
            ).mergeRequestParams({
                searchFields: ['code', 'name'],
                where: [
                    ['warehouse_id', '=', $wire.stockPosting.warehouse_id],
                    ['is_storage_location', '=', true],
                    ['is_active', '=', true],
                ],
            });
        },
        setTransferBinSearch() {
            const where = [
                ['warehouse_id', '=', $wire.stockTransfer.warehouse_id],
                ['is_active', '=', true],
            ];
            $tallstackuiSelect('transfer-from-bin-id').mergeRequestParams({
                searchFields: ['code', 'name'],
                where,
            });
            $tallstackuiSelect('transfer-to-bin-id').mergeRequestParams({
                searchFields: ['code', 'name'],
                where: [...where, ['is_storage_location', '=', true]],
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
                    :hint="__('Only bins marked as storage location can hold stock')"
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
    <x-modal
        id="transfer-stock-modal"
        x-on:open="
            $tallstackuiSelect('transfer-from-bin-id').clear();
            $tallstackuiSelect('transfer-to-bin-id').clear();
            setTransferBinSearch();
        "
        :title="__('Transfer Stock')"
    >
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                wire:model="stockTransfer.warehouse_id"
                :label="__('Warehouse')"
                required
                select="label:name|value:id"
                x-on:select="setTransferBinSearch()"
                :options="$warehouses"
            />
            <div id="transfer-from-bin-id">
                <x-select.styled
                    wire:model="stockTransfer.from_warehouse_bin_id"
                    :label="__('Source Bin')"
                    required
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
                                    $stockTransfer->warehouse_id,
                                ],
                                ['is_active', '=', true],
                            ],
                        ],
                    ]"
                />
            </div>
            <div id="transfer-to-bin-id">
                <x-select.styled
                    wire:model="stockTransfer.to_warehouse_bin_id"
                    :label="__('Target Bin')"
                    :hint="__('Only bins marked as storage location can hold stock')"
                    required
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
                                    $stockTransfer->warehouse_id,
                                ],
                                ['is_storage_location', '=', true],
                                ['is_active', '=', true],
                            ],
                        ],
                    ]"
                />
            </div>
            <x-select.styled
                wire:model="stockTransfer.lot_id"
                :label="__('Lot')"
                select="label:lot_number|value:id"
                :options="$lots"
            />
            <x-number wire:model="stockTransfer.amount" :label="__('Amount')" />
            <x-textarea
                wire:model="stockTransfer.description"
                :label="__('Description')"
            />
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$tsui.close.modal('transfer-stock-modal')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                x-on:click="
                    $wire.saveTransfer().then((success) => {
                        if (success) $tsui.close.modal('transfer-stock-modal');
                    })
                "
            />
        </x-slot:footer>
    </x-modal>
</div>
