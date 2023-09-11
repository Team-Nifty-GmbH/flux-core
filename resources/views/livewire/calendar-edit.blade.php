<div>
    @if($modal)
    <x-modal.card :title="__('Edit Calendar')" blur wire:model="editModal">
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <x-input wire:model.live="selectedCalendar.name" :label="__('Calendar Name')"/>
                        </div>
                        <div class="sm:col-span-6">
                            <x-color-picker
                                :label="__('Color')"
                                wire:model.live="selectedCalendar.color"
                            />
                        </div>
                        @if(! array_key_exists('module', $bluePrint))
                        <div class="sm:col-span-6">
                            <x-select wire:model="selectedCalendar.module" :label="__('Module')" :options="array_values($availableModules)" />
                        </div>
                        @endif
                        <div class="sm:col-span-6">
                            <x-select wire:model="selectedCalendar.parent_id" option-label="name" option-value="id" :label="__('Parent')" :options="$parentOptions" />
                        </div>
                        @if(! ($bluePrint['user_id'] ?? false))
                            <div class="sm:col-span-6">
                                <x-select wire:model.live="selectedCalendar.user_id" option-label="name" option-value="id" :label="__('User')" :options="$users" />
                            </div>
                        @endif
                        <div class="sm:col-span-6">
                            <x-checkbox wire:model="selectedCalendar.is_public" :label="__('Public')"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4" x-data="{deletable: @entangle('deletable').live}">

                <x-button
                    flat negative x-bind:class="deletable === false && 'invisible'" :label="__('Delete')" x-on:click="close" wire:click="delete" />

                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close" />
                    <x-button primary :label="__('Save')" wire:click="save" />
                </div>
            </div>
        </x-slot>
    </x-modal.card>
    @else
        <div class="space-y-8 divide-y divide-gray-200">
            <div class="space-y-8 divide-y divide-gray-200">
                <div>
                    <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <x-input wire:model.live="selectedCalendar.name" :label="__('Calendar Name')"/>
                        </div>
                        <div class="sm:col-span-6">
                            <x-color-picker
                                :label="__('Color')"
                                wire:model.live="selectedCalendar.color"
                            />
                        </div>
                        <div class="sm:col-span-6">
                            <x-select wire:model="selectedCalendar.module" :label="__('Module')" :options="array_values($availableModules)" />
                        </div>
                        <div class="sm:col-span-6">
                            <x-select wire:model="selectedCalendar.parent_id" option-label="name" option-value="id" :label="__('Parent')" :options="$parentOptions" />
                        </div>
                        <div class="sm:col-span-6">
                            <x-select wire:model.live="selectedCalendar.user_id" option-label="name" option-value="id" :label="__('User')" :options="$users" />
                        </div>
                        <div class="sm:col-span-6">
                            <x-checkbox wire:model="selectedCalendar.is_public" :label="__('Public')"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
