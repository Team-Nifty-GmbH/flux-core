<div
    tall-calendar
    x-data="{
        ...calendar(),
        @section('calendar-data')
        @show
    }"
>
    <div>
        @section('calendar-event-modal')
        <x-modal id="calendar-event-modal" :title="__('Edit Event')">
            <x-flux::calendar.event-edit />
            <x-slot:footer>
                <div class="flex w-full justify-between gap-2">
                    <div>
                        <x-button
                            x-show="calendarEvent.id"
                            spinner
                            flat
                            color="red"
                            :text="__('Delete')"
                            x-show="$wire.calendarEvent.is_editable && $wire.calendarEvent.id"
                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Calendar Event')]) }}"
                            wire:click="deleteEvent($wire.calendarEvent).then((calendarEvent) => {deleteEvent(calendarEvent);})"
                        />
                    </div>
                    <div class="flex gap-2">
                        <x-button
                            flat
                            :text="__('Cancel')"
                            x-on:click="$modalClose('calendar-event-modal')"
                        />
                        <x-button
                            primary
                            :text="__('Save')"
                            x-show="$wire.calendarEvent.is_editable"
                            x-on:click="
                                    $wire.confirmSave = $wire.calendarEventWasRepeatable && !$wire.calendarEvent.has_repeats ? 'this' : 'future';
                                    $wire.calendarEventWasRepeatable ?
                                        $wireui.confirmDialog({
                                            id: 'edit-repeatable-event-dialog',
                                            icon: 'question',
                                            accept: {
                                                label: '{{ __('OK') }}',
                                                execute: () => saveEvent()
                                            },
                                            reject: {
                                                label: '{{ __('Cancel') }}',
                                            }
                                        }) :
                                        saveEvent();
                                "
                        />
                    </div>
                </div>
            </x-slot>
        </x-modal>
        <x-dialog
            id="edit-repeatable-event-dialog"
            :title="__('Edit Repeatable Event')"
        >
            <div x-show="! $wire.calendarEvent.has_repeats">
                <x-radio
                    :label="__('This event')"
                    value="this"
                    wire:model="confirmSave"
                />
            </div>
            <x-radio
                :label="__('This event and following')"
                value="future"
                wire:model="confirmSave"
            />
            <x-radio
                :label="__('All events')"
                value="all"
                wire:model="confirmSave"
            />
        </x-dialog>
        <x-dialog id="delete-event-dialog" :title="__('Confirm Delete Event')">
            <div x-show="$wire.calendarEventWasRepeatable">
                <x-radio
                    :label="__('This event')"
                    value="this"
                    wire:model="confirmDelete"
                />
                <x-radio
                    :label="__('This event and following')"
                    value="future"
                    wire:model="confirmDelete"
                />
                <x-radio
                    :label="__('All events')"
                    value="all"
                    wire:model="confirmDelete"
                />
            </div>
        </x-dialog>
        @show
    </div>
    <livewire:calendar-overview
        :show-calendars="$showCalendars"
        :show-invites="$showInvites"
        :calendar-groups="$this->getCalendarGroups()"
    />
</div>
