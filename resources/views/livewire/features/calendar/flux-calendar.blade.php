<div tall-calendar x-data="{
        ...calendar(),
        @section('calendar-data')
        @show
    }"
     class="h-full"
>
    <div>
        @section('calendar-event-modal')
            <x-modal id="calendar-event-modal" :title="__('Edit Event')">
                <x-flux::calendar.event-edit />
                <x-slot:footer>
                    <div class="flex justify-between gap-2 w-full">
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
                            <x-button flat :text="__('Cancel')" x-on:click="$modalClose('calendar-event-modal')" />
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
                </x-slot:footer>
            </x-modal>
            <x-modal persistent id="edit-repeatable-event-dialog" :title="__('Edit Repeatable Event')">
                <div x-show="! $wire.calendarEvent.has_repeats">
                    <x-radio :label="__('This event')" value="this" wire:model="confirmSave"/>
                </div>
                <x-radio :label="__('This event and following')" value="future" wire:model="confirmSave"/>
                <x-radio :label="__('All events')" value="all" wire:model="confirmSave"/>
            </x-modal>
            <x-modal persistent id="delete-event-dialog" :title="__('Confirm Delete Event')">
                <div x-show="$wire.calendarEventWasRepeatable">
                    <x-radio :label="__('This event')" value="this" wire:model="confirmDelete"/>
                    <x-radio :label="__('This event and following')" value="future" wire:model="confirmDelete"/>
                    <x-radio :label="__('All events')" value="all" wire:model="confirmDelete"/>
                </div>
            </x-modal>
        @show
    </div>
    <x-card scope="min-h-full" class="lg:flex whitespace-nowrap">
        <livewire:calendar-overview
            :show-calendars="$showCalendars"
            :show-invites="$showInvites"
            :calendar-groups="$this->getCalendarGroups()"
        />
        <div wire:ignore class="w-full">
            <div class="dark:text-gray-50 border-l dark:border-secondary-600 !h-full" x-bind:id="id"></div>
        </div>
    </x-card>
</div>
