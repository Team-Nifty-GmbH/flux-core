@extends('tall-calendar::livewire.calendar.calendar-overview')
@section('calendar-edit')
    <div class="flex flex-col gap-4">
        <div x-cloak x-show="$wire.selectedCalendar.children == 0">
            <x-select
                wire:model="selectedCalendar.parentId"
                :label="__('Parent Calendar')"
                :options="$this->parentCalendars"
                option-label="name"
                option-value="id"
                option-description="description"
            />
        </div>
        <x-input wire:model="selectedCalendar.name" :label="__('Calendar Name')"/>
        <div x-cloak x-show="$wire.availableModels">
            <div x-bind:class="$wire.selectedCalendar.id && 'pointer-events-none'">
                <x-select
                    wire:model="selectedCalendar.modelType"
                    :label="__('Model Type')"
                    :options="$this->availableModels"
                    option-label="label"
                    option-value="value"
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
                <x-button.circle class="mr-2" primary icon="plus" wire:click="addCustomProperty" />
                @foreach($selectedCalendar['customProperties'] ?? [] as $index => $customProperty)
                    <div class="flex gap-x-4">
                        <div class="pt-6">
                            <x-button.circle negative icon="trash" wire:click="removeCustomProperty({{ $index }})" />
                        </div>
                        <div class="max-w-sm">
                            <x-select
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
@endsection
