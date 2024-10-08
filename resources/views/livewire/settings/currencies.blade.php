<x-modal wire:model="editModal">
    <x-card :title="($selectedCurrency->id ?? false) ? __('Edit Currency') : __('Create Currency')">
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-6">
                        <div class="space-y-3 sm:col-span-6">
                            <x-input wire:model="selectedCurrency.name" :label="__('Currency Name')"/>
                            <x-input wire:model="selectedCurrency.iso" :label="__('ISO')"/>
                            <x-input wire:model="selectedCurrency.symbol" :label="__('Symbol')"/>
                            <x-toggle wire:model.boolean="selectedCurrency.is_default" :label="__('Is Default')"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                @if(resolve_static(\FluxErp\Actions\Currency\DeleteCurrency::class, 'canPerformAction', [false]))
                    <div x-bind:class="$wire.selectedCurrency.id > 0 || 'invisible'">
                        <x-button
                            flat
                            negative
                            :label="__('Delete')"
                            wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Currency')]) }}"
                            wire:click="delete().then((success) => {if(success) close();});"
                        />
                    </div>
                @endif
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close"/>
                    <x-button primary :label="__('Save')" wire:click="save().then((success) => {if(success) close();});"/>
                </div>
            </div>
        </x-slot>
    </x-card>
</x-modal>
