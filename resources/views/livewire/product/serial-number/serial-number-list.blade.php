<x-modal id="create-serial-number-modal">
    <div class="flex flex-col gap-1.5">
        <x-select.styled
            :label="__('Product')"
            wire:model="stockPosting.product_id"
            required
            select="label:name|value:id|description:product_number"
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
            required
            select="label:label|value:id"
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
        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$closeModal('create-serial-number-modal')"/>
        <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $closeModal('create-serial-number-modal')})"/>
    </x-slot:footer>
</x-modal>
