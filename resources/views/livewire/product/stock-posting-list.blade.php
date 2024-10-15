<x-modal name="create-stock-posting">
    <x-card>
        <div class="flex flex-col gap-4">
            <x-select
                wire:model="stockPosting.warehouse_id"
                :label="__('Warehouse')"
                :clearable="false"
                :options="$warehouses"
                option-key-value
            />
            <x-inputs.number wire:model="stockPosting.posting" :label="__('Posting')" />
            <x-inputs.number wire:model="stockPosting.purchase_price" :label="__('Purchase Price')" />
            <x-textarea wire:model="stockPosting.description" :label="__('Description')" />
            @if($hasSerialNumbers)
                <hr />
                <x-select
                    wire:model="stockPosting.serial_number.serial_number_range_id"
                    :label="__('Serial Number Range')"
                    :options="$serialNumberRanges"
                    option-key-value
                />
                <x-input wire:model="stockPosting.serial_number.serial_number" :label="__('Serial Number')" />
                <x-input wire:model="stockPosting.serial_number.supplier_serial_number" :label="__('Supplier Serial Number')" />
                <x-toggle wire:model="stockPosting.serial_number.use_supplier_serial_number" :label="__('Use Supplier Serial Number')" />
            @endif
        </div>
        <x-slot:footer>
            <div class="flex justify-end gap-1.5">
                <x-button flat :label="__('Cancel')" x-on:click="close"/>
                <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
