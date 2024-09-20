<x-modal.card z-index="z-30" wire:model="showAdditionalColumnModal" :title="$create ? __('Create Additional Column') : __('Edit Additional Column')">
    <livewire:settings.additional-column-edit/>
    <x-slot name="footer">
        <div x-data="{create: $wire.entangle('create')}" class="w-full">
            <div
                class="flex justify-between gap-x-4">
                @if(resolve_static(\FluxErp\Actions\AdditionalColumn\DeleteAdditionalColumn::class, 'canPerformAction', [false]))
                    <x-button x-bind:class="! create || 'invisible'" flat negative label="{{ __('Delete') }}"
                              x-on:click="window.$wireui.confirmDialog({
                                                            title: '{{ __('Delete additional column') }}',
                                                            description: '{{ __('Do you really want to delete this additional column?') }}',
                                                            icon: 'error',
                                                            accept: {
                                                                label: '{{ __('Delete') }}',
                                                                method: 'delete',
                                                            },
                                                            reject: {
                                                                label: '{{ __('Cancel') }}',
                                                            }
                                                        }, $wire.__instance.id)
                                                        " label="{{ __('Delete') }}"/>
                @endif
                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close"/>
                    <x-button primary :label="__('Save')" wire:click="$dispatchTo('settings.additional-column-edit', 'save')"/>
                </div>
            </div>
        </div>
    </x-slot>
</x-modal.card>
