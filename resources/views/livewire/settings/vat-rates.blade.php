<x-modal id="edit-vat-rate-modal">
    <div class="flex flex-col gap-4">
        <x-input wire:model="vatRate.name" :label="__('Name')" />
        <x-number wire:model="vatRate.rate_percentage_frontend" :label="__('Rate Percentage')" />
        <x-flux::editor wire:model="vatRate.footer_text" :label="__('Footer Text')" />
        <x-toggle wire:model.boolean="vatRate.is_default" :label="__('Is Default')"/>
        <x-toggle wire:model.boolean="vatRate.is_tax_exemption" :label="__('Is Tax Exemption')"/>
    </div>
    <x-slot:footer>
        <div class="flex justify-between gap-x-4">
            @if(resolve_static(\FluxErp\Actions\VatRate\DeleteVatRate::class, 'canPerformAction', [false]))
                <div x-bind:class="$wire.vatRate.id > 0 || 'invisible'">
                    <x-button
                        flat
                        color="red"
                        :text="__('Delete')"
                        wire:click="delete().then((success) => { if(success) $modalClose('edit-vat-rate-modal')})"
                        wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Vat Rate')]) }}"
                    />
                </div>
            @endif
            <div class="flex">
                <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-vat-rate-modal')"/>
                <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-vat-rate-modal')})"/>
            </div>
        </div>
    </x-slot:footer>
</x-modal>
