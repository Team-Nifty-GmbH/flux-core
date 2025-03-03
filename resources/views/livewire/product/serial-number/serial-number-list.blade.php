<x-modal id="create-serial-number">
    <div class="flex flex-col gap-4">
        <x-select.styled
            :label="__('Product')"
            wire:model="stockPosting.product_id"
            option-description="product_number"
            required
            :request="[
                'url' => route('search', \FluxErp\Models\Product::class),
                'method' => 'POST',
                'params' => [
                    'whereDoesntHave' => 'children',
                    'fields' => [
                        'id',
                        'name',
                        'product_number',
                    ],
                    'with' => 'media',
                ],
            ]"
        />
        <x-input wire:model="stockPosting.serial_number.serial_number" :label="__('Serial Number')" />
        <x-input wire:model="stockPosting.serial_number.supplier_serial_number" :label="__('Supplier Serial Number')" />
        <x-toggle wire:model="stockPosting.serial_number.use_supplier_serial_number" :label="__('Use Supplier Serial Number')" />
        <x-number wire:model="stockPosting.purchase_price" :label="__('Purchase Price')" />
        <x-select.styled
            :label="__('Address')"
            wire:model="stockPosting.address.id"
            option-description="description"
            required
            :request="[
                'url' => route('search', \FluxErp\Models\Address::class),
                'method' => 'POST',
                'params' => [
                    'with' => 'contact.media',
                ],
            ]"
        />
        <x-number wire:model="stockPosting.address.quantity" :label="__('Quantity')" />
    </div>
    <x-slot:footer>
        <div class="flex justify-end gap-1.5">
            <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$closeModal('create-serial-number')"/>
            <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $closeModal('create-serial-number')})"/>
        </div>
    </x-slot:footer>
</x-modal>
