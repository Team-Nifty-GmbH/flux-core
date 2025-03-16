<div class="py-6">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">{{ __('Clients') }}</h1>
                <div class="mt-2 text-sm text-gray-300">{{ __('Here you can manage your clients...') }}</div>
            </div>
        </div>
        <div wire:ignore>
            @include('tall-datatables::livewire.data-table')
        </div>
    </div>
    <x-modal size="6xl" id="edit-client">
        <x-flux::tabs
            :$tabs
            wire:model="tab"
            wire:loading
        >
            @includeWhen($tab === 'general', 'flux::components.settings.client.general')
        </x-flux::tabs>
        <x-slot:footer>
            <div class="w-full">
                <div class="flex justify-between gap-x-4">
                    @if(resolve_static(\FluxErp\Actions\Client\DeleteClient::class, 'canPerformAction', [false]))
                        <x-button
                            light
                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Client')]) }}"
                            x-bind:class="$wire.client.id > 0 || 'invisible'"
                            wire:click="delete().then((success) => {if(success) close();});"
                            flat
                            color="red"
                            :text="__('Delete')"
                        />
                    @endif
                    <div class="flex gap-x-2">
                        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-client')"/>
                        <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => {if(success) $modalClose('edit-client');});"/>
                    </div>
                </div>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
