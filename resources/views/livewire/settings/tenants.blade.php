<div class="py-6">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-xl font-semibold dark:text-white">
                    {{ __('Tenants') }}
                </h1>
                <div class="mt-2 text-sm text-gray-300">
                    {{ __('Here you can manage your tenants...') }}
                </div>
            </div>
        </div>
        <div wire:ignore>
            @include('tall-datatables::livewire.data-table')
        </div>
    </div>
    <x-modal size="6xl" id="edit-tenant" :title="__('Tenant')">
        <x-flux::tabs :$tabs wire:model="tab" wire:loading>
            @includeWhen($tab === 'general', 'flux::components.settings.tenant.general')
        </x-flux::tabs>
        <x-slot:footer>
            <div class="w-full">
                <div class="flex justify-between gap-x-4">
                    @if (resolve_static(\FluxErp\Actions\Tenant\DeleteTenant::class, 'canPerformAction', [false]))
                        <x-button
                            light
                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Tenant')]) }}"
                            x-bind:class="$wire.tenant.id > 0 || 'invisible'"
                            wire:click="delete().then((success) => {if(success) close();});"
                            flat
                            color="red"
                            :text="__('Delete')"
                        />
                    @endif

                    <div class="flex gap-x-2">
                        <x-button
                            color="secondary"
                            light
                            flat
                            :text="__('Cancel')"
                            x-on:click="$modalClose('edit-tenant')"
                        />
                        <x-button
                            color="indigo"
                            :text="__('Save')"
                            wire:click="save().then((success) => {if(success) $modalClose('edit-tenant');});"
                        />
                    </div>
                </div>
            </div>
        </x-slot>
    </x-modal>
</div>
