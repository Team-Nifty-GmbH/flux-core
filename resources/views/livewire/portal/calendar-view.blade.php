@extends('tall-calendar::livewire.calendar.calendar')
@section('calendar-data')
    attendEvent() {
        $wire.attendEvent(this.calendarEvent.id).then(() => {
            this.calendarEvent.is_attending = true;
            this.close();
        });
    },
    notAttendEvent() {
        $wire.notAttendEvent(this.calendarEvent.id).then(() => {
            this.calendarEvent.is_attending = false;
            this.close();
        });
    },
    dateClick() {
    }
@endsection
@section('calendar-event-modal')
    <x-modal-card :title="__('Edit Event')" x-on:close="this.calendarEventItemProxy = {};">
        <x-tall-calendar::event-edit />
        <x-button :label="__('Attend')" positive x-on:click="attendEvent()" x-show="calendarEvent.id && !calendarEvent.is_attending" />
        <x-button :label="__('Not attend')" x-on:click="notAttendEvent()" negative x-show="calendarEvent.id && calendarEvent.is_attending" />
    </x-modal-card>
@endsection
@section('calendar-list')
    <x-tall-calendar::calendar-list group="public" />
@endsection
