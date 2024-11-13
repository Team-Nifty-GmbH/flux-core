<div class="flex flex-col" x-data="{edit: false}">
    <x-modal name="edit-dashboard">
        <x-card>
            <div class="flex flex-col gap-4">
                @canAction(\FluxErp\Actions\Dashboard\CreateDashboard::class)
                    <x-toggle :label="__('Create my own dashboard')" wire:model="dashboardForm.createOwn"/>
                @endCanAction
                <div class="flex flex-col gap-4" x-cloak x-show="$wire.dashboardForm.createOwn">
                    <x-input :label="__('Name')" wire:model="dashboardForm.name" />
                    <x-toggle :label="__('Public')" wire:model="dashboardForm.is_public" />
                </div>
                <div class="flex flex-col gap-4" x-cloak x-show="! $wire.dashboardForm.createOwn">
                    <template x-for="publicDashboard in $wire.publicDashboards">
                        <div
                            x-on:click="$wire.selectPublicDashboard(publicDashboard.id).then((success) => {if(success) close()})"
                            class="w-full cursor-pointer mb-2 p-2 border rounded hover:bg-gray-100 dark:hover:bg-secondary-900"
                        >
                            <span x-text="publicDashboard.name"></span>
                        </div>
                    </template>
                    <x-toggle wire:model="dashboardForm.copyPublic" :label="__('Copy public dashboard')"/>
                </div>
            </div>
            <x-slot:footer>
                <div class="flex justify-between gap-1.5">
                    <x-button
                        label="{{ __('Cancel') }}"
                        x-on:click="close()"
                    />
                    <div>
                        @canAction(\FluxErp\Actions\Dashboard\DeleteDashboard::class)
                            <x-button
                                negative
                                label="{{ __('Delete') }}"
                                wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => 'Dashboard']) }}"
                                wire:click="delete($wire.dashboardForm.id).then((success) => { if(success) close(); })"
                                x-cloak
                                x-show="$wire.dashboardForm.id"
                            />
                        @endCanAction
                        <x-button
                            primary
                            label="{{ __('Save') }}"
                            wire:click="save().then((success) => {if(success) close();})"
                        />
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <div class="pb-6 md:flex md:items-center md:justify-between md:space-x-5">
        <div class="flex items-start space-x-5">
            <div class="flex-shrink-0">
                <x-avatar :src="auth()->user()->getAvatarUrl()" />
            </div>
            <div class="pt-1.5">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">{{ __('Hello') }} {{ Auth::user()->name }}</h1>
            </div>
        </div>
    </div>
    <div class="pb-2.5">
        <div class="dark:border-secondary-700 border-b border-gray-200 flex justify-between">
            <nav class="soft-scrollbar flex overflow-x-auto" x-ref="tabButtons">
                <x-button
                    :label="__('Default')"
                    flat
                    class="border-b-2 border-b-transparent focus:!ring-0 focus:!ring-offset-0"
                    x-on:click="$wire.$set('dashboardId', null, true)"
                    x-bind:class="{'!border-b-primary-600 rounded-b-none': $wire.dashboardId === null}"
                />
                <div x-sort="$wire.reOrder($item, $position);">
                    <template x-for="dashboard in $wire.dashboards">
                        <x-button
                            x-sort:item="dashboard.id"
                            flat
                            class="border-b-2 border-b-transparent focus:!ring-0 focus:!ring-offset-0"
                            x-on:click="edit ? $wire.edit(dashboard.id) : $wire.$set('dashboardId', dashboard.id, true)"
                            x-bind:class="{'!border-b-primary-600 rounded-b-none': $wire.dashboardId === dashboard.id }"
                        >
                            <x-slot:label>
                                <i x-show="dashboard.is_public" x-cloak class="ph ph-rss"></i>
                                <span x-text="dashboard.name"></span>
                            </x-slot:label>
                        </x-button>
                    </template>
                </div>
                @canAction(\FluxErp\Actions\Dashboard\CreateDashboard::class)
                    <x-button
                        flat
                        x-cloak
                        x-show="edit"
                        icon="plus"
                        wire:click="edit"
                    />
                @endCanAction
            </nav>
            @canAction(\FluxErp\Actions\Dashboard\UpdateDashboard::class)
                <div>
                    <x-button icon="pencil" x-cloak x-show="! edit" x-on:click="edit = true"/>
                    <x-button :label="__('Done')" x-cloak x-show="edit" x-on:click="edit = false"/>
                </div>
            @endCanAction
        </div>
    </div>
    <div class="w-full">
        <livewire:features.dashboard :dashboard-id="$dashboardId" wire:key="{{ uniqid() }}"/>
    </div>
</div>
