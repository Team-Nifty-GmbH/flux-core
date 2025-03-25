<div>
    <x-modal id="edit-event-modal" scope="headless" persistent>
        <div>
            <livewire:dynamic-component
                wire:model="event"
                :is="$event->edit_component ?? 'features.calendar.calendar-event'"
            />
        </div>
    </x-modal>
</div>
