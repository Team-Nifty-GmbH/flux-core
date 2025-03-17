@extends('flux::livewire.features.calendar.flux-calendar')
@section('calendar-data')
        attendEvent() { $wire.attendEvent(this.calendarEvent.id).then(() => {
        this.calendarEvent.is_attending = true; this.close(); }); },
        notAttendEvent() { $wire.notAttendEvent(this.calendarEvent.id).then(()
        => { this.calendarEvent.is_attending = false; this.close(); }); },
        dateClick() { }
@endsection

@section('calendar-event-modal')
    <x-modal
        :title="__('Edit Event')"
        x-on:close="this.calendarEventItemProxy = {};"
    >
        <x-flux::calendar.event-edit />
        <x-button
            :text="__('Attend')"
            color="emerald"
            x-on:click="attendEvent()"
            x-show="calendarEvent.id && !calendarEvent.is_attending"
        />
        <x-button
            :text="__('Not attend')"
            x-on:click="notAttendEvent()"
            color="red"
            x-show="calendarEvent.id && calendarEvent.is_attending"
        />
    </x-modal>
@endsection

@section('calendar-list')
    <x-flux::calendar.calendar-list group="public" />
@endsection
