<x-modal name="edit-warehouse">
    <x-card>
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
                            negative
                            :label="__('Delete')"
                            x-on:click="close"
                            wire:click="delete().then((success) => { if(success) close()})"
                            wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Warehouse')]) }}"
                        />
                    </div>
                @endif
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close"/>
                    <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
                </div>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
