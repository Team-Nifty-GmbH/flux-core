<x-modal id="create-stock-posting">
    <div class="flex flex-col gap-4">
        <x-select.styled
            wire:model="stockPosting.warehouse_id"
            :label="__('Warehouse')"
            required
            :options="$warehouses"
            option-key-value
        />
        <x-number wire:model="stockPosting.posting" :label="__('Posting')" />
        <x-number wire:model="stockPosting.purchase_price" :label="__('Purchase Price')" />
        <x-textarea wire:model="stockPosting.description" :label="__('Description')" />
        @if($hasSerialNumbers)
            <hr />
            <x-select.styled
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
            <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('create-stock-posting')"/>
            <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('create-stock-posting')})"/>
        </div>
    </x-slot:footer>
</x-modal>
