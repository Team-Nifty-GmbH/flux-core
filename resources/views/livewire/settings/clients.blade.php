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
    <x-modal max-width="6xl" name="edit-client">
        <x-card>
            <x-tabs
                :$tabs
                wire:model="tab"
                wire:loading
            >
                @includeWhen($tab === 'general', 'flux::components.settings.client.general')
            </x-tabs>
            <x-slot:footer>
                <div class="w-full">
                    <div class="flex justify-between gap-x-4">
                        @if(resolve_static(\FluxErp\Actions\Client\DeleteClient::class, 'canPerformAction', [false]))
                            <x-button
                                wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Client')]) }}"
                                x-bind:class="$wire.client.id > 0 || 'invisible'"
                                wire:click="delete().then((success) => {if(success) close();});"
                                flat
                                negative
                                :label="__('Delete')"
                            />
                        @endif
                        <div class="flex">
                            <x-button flat :label="__('Cancel')" x-on:click="close"/>
                            <x-button primary :label="__('Save')" wire:click="save().then((success) => {if(success) close();});"/>
                        </div>
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
</div>
