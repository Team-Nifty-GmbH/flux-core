@extends('tall-calendar::livewire.calendar.calendar-overview')
@section('calendar-modal')
    <x-modal id="calendar-modal" :title="__('Edit Calendar')">
        @section('calendar-edit')
            <div class="flex flex-col gap-4">
                <div x-cloak x-show="$wire.selectedCalendar.children === undefined || $wire.selectedCalendar.children?.length() === 0">
                    <x-select.styled
                        wire:model="selectedCalendar.parentId"
                        :label="__('Parent Calendar')"
                        :options="$this->parentCalendars"
                        select="label:name|value:id"
                        option-description="description"
                    />
                </div>
                <x-input wire:model="selectedCalendar.name" :label="__('Calendar Name')"/>
                <div x-cloak x-show="$wire.availableModels">
                    <div x-bind:class="$wire.selectedCalendar.id && 'pointer-events-none'">
                        <x-select.styled
                            wire:model="selectedCalendar.modelType"
                            :label="__('Model Type')"
                            :options="$this->availableModels"
                            select="label:value|value:label"
                            x-bind:disabled="$wire.selectedCalendar.id"
                        />
                    </div>
                </div>
                <x-input
                    class="p-0"
                    type="color"
                    :label="__('Color')"
                    wire:model="selectedCalendar.color"
                />
                <x-checkbox wire:model="selectedCalendar.hasRepeatableEvents" :label="__('Has repeatable events')"/>
                <x-checkbox wire:model="selectedCalendar.isPublic" :label="__('Public')"/>
                <x-card :title="__('Custom Properties')">
                    <div class="flex flex-col gap-4">
                        <x-button.circle class="mr-2" color="indigo" icon="plus" wire:click="addCustomProperty" />
                        @foreach($selectedCalendar['customProperties'] ?? [] as $index => $customProperty)
                            <div class="flex gap-x-4">
                                <div class="pt-6">
                                    <x-button.circle color="red" icon="trash" wire:click="removeCustomProperty({{ $index }})" />
                                </div>
                                <div class="max-w-sm">
                                    <x-select.styled
                                        wire:model="selectedCalendar.customProperties.{{ $index }}.field_type"
                                        :label="__('Field Type')"
                                        :options="$this->fieldTypes"
                                        option-key-value
                                    />
                                </div>
                                <div class="w-full">
                                    <x-input
                                        wire:model="selectedCalendar.customProperties.{{ $index }}.name"
                                        :label="__('Name')"
                                    />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>
        @show
        <x-slot:footer>
            <div class="flex justify-between gap-x-4">
                <div>
                    <x-button
                        x-show="$wire.selectedCalendar.id && '{{ resolve_static(\FluxErp\Actions\Calendar\DeleteCalendar::class, 'canPerformAction', [false]) }}'"
                        flat
                        color="red"
                        :text="__('Delete')" x-on:click="deleteCalendar()"
                    />
                </div>
                <div class="flex">
                    <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('calendar-modal');" />
                    <x-button color="indigo" :text="__('Save')" x-on:click="saveCalendar()" />
                </div>
            </div>
        </x-slot:footer>
    </x-modal>
@endsection

