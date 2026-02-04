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
        <x-tab wire:model="tab">
            <x-tab.items tab="general" :title="__('General')">
                @include('flux::components.settings.tenant.general')
            </x-tab.items>
            <x-tab.items tab="logos" :title="__('Logos')">
                @include('flux::components.settings.tenant.logos')
            </x-tab.items>
            <x-tab.items tab="terms-and-conditions" :title="__('Terms and Conditions')">
                @include('flux::components.settings.tenant.terms-and-conditions')
            </x-tab.items>
            <x-tab.items tab="sepa" :title="__('SEPA')">
                @include('flux::components.settings.tenant.sepa')
            </x-tab.items>
        </x-tab>
        <x-slot:footer>
            <div class="w-full">
                <div class="flex justify-between gap-x-4">
                    @if (resolve_static(\FluxErp\Actions\Tenant\DeleteTenant::class, 'canPerformAction', [false]))
                        <x-button
                            light
                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Tenant')]) }}"
                            x-bind:class="$wire.tenant.id > 0 || 'invisible'"
                            wire:click="delete().then((success) => {if(success) $modalClose('edit-tenant');});"
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
