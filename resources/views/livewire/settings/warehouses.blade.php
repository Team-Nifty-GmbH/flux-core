<x-modal id="edit-warehouse-modal">
    <div class="flex flex-col gap-4">
        <x-input wire:model="warehouse.name" :label="__('Name')" />
        <x-toggle wire:model.boolean="warehouse.is_default" :label="__('Is Default')" />
    </div>
    <x-slot:footer>
        <div class="flex justify-between gap-x-4">
            @if(resolve_static(\FluxErp\Actions\Warehouse\DeleteWarehouse::class, 'canPerformAction', [false]))
                <div x-bind:class="$wire.warehouse.id > 0 || 'invisible'">
                    <x-button
                        flat
                        color="red"
                        :text="__('Delete')"
                        wire:click="delete().then((success) => { if(success) $modalClose('edit-warehouse-modal')})"
                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Warehouse')]) }}"
                    />
                </div>
            @endif
            <div class="flex">
                <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-warehouse-modal')"/>
                <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-warehouse-modal')})"/>
            </div>
        </div>
    </x-slot:footer>
</x-modal>
