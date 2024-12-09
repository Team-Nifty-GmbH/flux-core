<x-modal name="edit-vat-rate">
    <x-card class="w-full">
        <div class="flex flex-col gap-4">
            <x-input wire:model="vatRate.name" :label="__('Name')" />
            <x-number wire:model="vatRate.rate_percentage_frontend" :label="__('Rate Percentage')" />
            <x-flux::editor wire:model="vatRate.footer_text" :label="__('Footer Text')" />
            <x-toggle wire:model.boolean="vatRate.is_default" :label="__('Is Default')"/>
        </div>
        <x-slot:footer>
            <div class="flex justify-between gap-x-4">
                @if(resolve_static(\FluxErp\Actions\VatRate\DeleteVatRate::class, 'canPerformAction', [false]))
                    <div x-bind:class="$wire.vatRate.id > 0 || 'invisible'">
                        <x-button
                            flat
                            negative
                            :label="__('Delete')"
                            x-on:click="close"
                            wire:click="delete().then((success) => { if(success) close()})"
                            wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Vat Rate')]) }}"
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
