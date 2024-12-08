<x-modal name="create-serial-number">
    <x-card>
        <div class="flex flex-col gap-4">
            <x-select
                :label="__('Product')"
                wire:model="stockPosting.product_id"
                option-value="id"
                option-label="label"
                option-description="product_number"
                :clearable="false"
                :template="[
                    'name' => 'user-option',
                ]"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Product::class),
                    'params' => [
                        'whereDoesntHave' => 'children',
                        'fields' => ['id', 'name', 'product_number'],
                        'with' => 'media',
                    ]
                ]"
            />
            <x-input wire:model="stockPosting.serial_number.serial_number" :label="__('Serial Number')" />
            <x-input wire:model="stockPosting.serial_number.supplier_serial_number" :label="__('Supplier Serial Number')" />
            <x-toggle wire:model="stockPosting.serial_number.use_supplier_serial_number" :label="__('Use Supplier Serial Number')" />
            <x-number wire:model="stockPosting.purchase_price" :label="__('Purchase Price')" />
            <x-select
                :label="__('Address')"
                wire:model="stockPosting.address.id"
                option-value="id"
                option-label="label"
                option-description="description"
                :clearable="false"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Address::class),
                    'method' => 'POST',
                    'params' => [
                        'with' => 'contact.media',
                    ]
                ]"
            />
            <x-number wire:model="stockPosting.address.quantity" :label="__('Quantity')" />
        </div>
        <x-slot:footer>
            <div class="flex justify-end gap-1.5">
                <x-button flat :label="__('Cancel')" x-on:click="close"/>
                <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
