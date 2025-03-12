<x-modal id="edit-vat-rate-modal">
    <div class="flex flex-col gap-1.5">
        @section('settings.vat-rates.inputs')
            <x-input wire:model="vatRate.name" :label="__('Name')" />
            <x-number wire:model="vatRate.rate_percentage_frontend" :label="__('Rate Percentage')" />
            <x-flux::editor wire:model="vatRate.footer_text" :label="__('Footer Text')" />
            <div class="mt-2">
                <x-toggle wire:model.boolean="vatRate.is_default" :label="__('Is Default')"/>
            </div>
            <x-toggle wire:model.boolean="vatRate.is_tax_exemption" :label="__('Is Tax Exemption')"/>
        @show
    </div>
    <x-slot:footer>
        @section('settings.vat-rates.buttons')
            <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-vat-rate-modal')"/>
            <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-vat-rate-modal')})"/>
        @show
    </x-slot:footer>
</x-modal>
