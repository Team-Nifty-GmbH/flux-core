<x-modal id="edit-additional-column-modal" z-index="z-30" wire="showAdditionalColumnModal" :title="$create ? __('Create Additional Column') : __('Edit Additional Column')">
    <livewire:settings.additional-column-edit/>
    <x-slot name="footer">
        <div x-data="{create: $wire.entangle('create')}" class="w-full">
            <div
                class="flex justify-between gap-x-4">
                @if(resolve_static(\FluxErp\Actions\AdditionalColumn\DeleteAdditionalColumn::class, 'canPerformAction', [false]))
                    <x-button
                        x-bind:class="! create || 'invisible'"
                        flat color="red" :text="__('Delete') "
                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Additional Column')]) }}"
                        wire:click="delete"
                        :text="__('Delete')"
                    />
                @endif
                <div class="flex">
                    <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-additional-column-modal')"/>
                    <x-button color="indigo" :text="__('Save')" wire:click="$dispatchTo('settings.additional-column-edit', 'save')"/>
                </div>
            </div>
        </div>
    </x-slot>
</x-modal>
