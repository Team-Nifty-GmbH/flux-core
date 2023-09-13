<div class="py-6" x-data="{customerPortalUrl: '{{ route('settings.customer-portal', ['client' => ':clientId']) }}' }">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold">{{ __('Clients') }}</h1>
                <div class="mt-2 text-sm text-gray-300">{{__('Here you can manage your clients...')}}</div>
            </div>
        </div>
        @include('tall-datatables::livewire.data-table')
    </div>

    <x-modal.card max-width="6xl" z-index="z-30" wire:model="showClientModal" :title="__('Edit Client')">
        <livewire:settings.client-edit/>
        <x-slot name="footer">
            <div x-data="{create: @entangle('create')}" class="w-full">
                <div
                    class="flex justify-between gap-x-4">
                    @if(user_can('action.client.delete'))
                        <x-button x-bind:class="! create || 'invisible'" flat negative label="{{ __('Delete') }}"
                                  x-on:click="window.$wireui.confirmDialog({
                                                            title: '{{ __('Delete client') }}',
                                                            description: '{{ __('Do you really want to delete this client?') }}',
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
                        <x-button primary :label="__('Save')" wire:click="$dispatchTo('settings.client-edit', 'save')"/>
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal.card>
    <x-modal.card wire:model="showClientLogosModal" :title="__('Manage Logos')">
        <livewire:settings.client-logos/>
        <x-slot name="footer">
            <div class="w-full">
                    <div class="flex justify-end gap-x-4">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="$dispatchTo('settings.client-logos', 'save')"/>
                    </div>
            </div>
        </x-slot>
    </x-modal.card>
</div>
