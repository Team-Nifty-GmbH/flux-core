<div class="flex flex-col" x-data="{edit: false}">
    <x-modal name="edit-dashboard">
        <x-card>
            <div class="flex flex-col gap-4">
                <x-input :label="__('Name')" wire:model="dashboardForm.name" />
                <x-toggle :label="__('Public')" wire:model="dashboardForm.is_public" />
            </div>
            <x-slot:footer>
                <div class="flex justify-between gap-1.5">
                    <x-button
                        label="{{ __('Cancel') }}"
                        x-on:click="close()"
                    />
                    <x-button
                        primary
                        label="{{ __('Save') }}"
                        wire:click="save"
                    />
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
                @foreach($dashboards as $dashboardItem)
                    <x-button
                        :label="$dashboardItem['name']"
                        flat
                        class="border-b-2 border-b-transparent focus:!ring-0 focus:!ring-offset-0"
                        wire:click="$set('dashboardId', {{ $dashboardItem['id'] ?? 'null' }}, true)"
                        x-bind:class="{'!border-b-primary-600 rounded-b-none': $wire.dashboardId === {{ $dashboardItem['id'] ?? 'null' }} }"
                    />
                @endforeach
                <x-button
                    x-cloak
                    x-show="edit"
                    icon="plus"
                    wire:click="edit"
                />
            </nav>
            <div>
                <x-button icon="pencil" x-on:click="edit = ! edit"/>
            </div>
        </div>
    </div>
    <div class="w-full container">
        <livewire:features.dashboard :dashboard-id="$dashboardId" wire:key="{{ uniqid() }}"/>
    </div>
</div>
