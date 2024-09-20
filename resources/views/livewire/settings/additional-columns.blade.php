<x-modal.card z-index="z-30" wire:model="showAdditionalColumnModal" :title="$create ? __('Create Additional Column') : __('Edit Additional Column')">
    <livewire:settings.additional-column-edit/>
    <x-slot name="footer">
        <div x-data="{create: $wire.entangle('create')}" class="w-full">
            <div
                class="flex justify-between gap-x-4">
                @if(resolve_static(\FluxErp\Actions\AdditionalColumn\DeleteAdditionalColumn::class, 'canPerformAction', [false]))
                    <x-button
                        x-bind:class="! create || 'invisible'"
                        flat negative label="{{ __('Delete') }}"
                        wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Additional Column')]) }}"
                        wire:click="delete"
                        label="{{ __('Delete') }}"
                    />
                @endif
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close"/>
                    <x-button primary :label="__('Save')" wire:click="$dispatchTo('settings.additional-column-edit', 'save')"/>
                </div>
            </div>
        </div>
    </x-slot>
</x-modal.card>
