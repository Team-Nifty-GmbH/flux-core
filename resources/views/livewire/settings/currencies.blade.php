<x-modal id="edit-currency-modal" wire="editModal" :title="($selectedCurrency->id ?? false) ? __('Edit Currency') : __('Create Currency')">
    <div class="flex flex-col gap-1.5">
        <x-input wire:model="selectedCurrency.name" :label="__('Currency Name')"/>
        <x-input wire:model="selectedCurrency.iso" :label="__('ISO')"/>
        <x-input wire:model="selectedCurrency.symbol" :label="__('Symbol')"/>
        <div class="mt-2">
            <x-toggle wire:model.boolean="selectedCurrency.is_default" :label="__('Is Default')"/>
        </div>
    </div>
    <x-slot:footer>
        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-currency-modal')"/>
        <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => {if(success) $modalClose('edit-currency-modal');});"/>
    </x-slot:footer>
</x-modal>
