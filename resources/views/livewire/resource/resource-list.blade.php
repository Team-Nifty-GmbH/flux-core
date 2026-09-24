<div>
    <x-modal
        :id="$resourceForm->modalName()"
        size="2xl"
        :title="__('Resource')"
    >
        <div class="flex flex-col gap-4">
            <x-input
                wire:model="resourceForm.name"
                :label="__('Name')"
                required
            />

            <x-input
                wire:model="resourceForm.resource_number"
                :label="__('Resource Number')"
            />

            <x-select.styled
                wire:model="resourceForm.product_id"
                :label="__('Product')"
                :placeholder="__('Select')"
                select="value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Product::class),
                    'method' => 'POST',
                ]"
            />

            <x-toggle
                wire:model="resourceForm.allow_overbooking"
                :label="__('Allow Overbooking')"
            />

            <x-toggle
                wire:model="resourceForm.is_active"
                :label="__('Active')"
            />

            <x-textarea
                wire:model="resourceForm.description"
                :label="__('Description')"
            />
        </div>

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$tsui.close.modal('{{ $resourceForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                x-on:click="$wire.save().then((success) => { if(success) $tsui.close.modal('{{ $resourceForm->modalName() }}') })"
            />
        </x-slot:footer>
    </x-modal>
</div>
