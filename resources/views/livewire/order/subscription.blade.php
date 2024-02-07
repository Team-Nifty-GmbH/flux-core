@extends('flux::livewire.order.order')
@section('modals')
    @parent
    <x-modal name="edit-schedule">
        <x-card :title="__('Edit Schedule')">
            <div class="flex flex-col gap-1.5">
                <x-select
                    :label="__('Order type')"
                    :options="$orderTypes"
                    option-label="name"
                    option-value="id"
                    :clearable="false"
                    wire:model="schedule.parameters.orderType"
                />
                <x-select
                    :label="__('Repeat')"
                    :options="$frequencies"
                    option-value="name"
                    option-label="label"
                    autocomplete="off"
                    :clearable="false"
                    wire:model="schedule.cron.methods.basic"
                />
                <div x-cloak x-show="['dailyAt', 'lastDayOfMonth'].indexOf($wire.schedule.cron.methods.basic) >= 0">
                    <x-time-picker
                        :label="__('Time')"
                        format="24"
                        wire:model="schedule.cron.parameters.basic.0"
                    />
                </div>
                <div x-cloak x-show="$wire.schedule.cron.methods.basic === 'weeklyOn'" class="flex flex-col gap-4">
                    <x-select
                        :label="__('Weekday')"
                        :options="[
                                ['id' => 1, 'name' => __('Mondays')],
                                ['id' => 2, 'name' => __('Tuesdays')],
                                ['id' => 3, 'name' => __('Wednesdays')],
                                ['id' => 4, 'name' => __('Thursdays')],
                                ['id' => 5, 'name' => __('Fridays')],
                                ['id' => 6, 'name' => __('Saturdays')],
                                ['id' => 0, 'name' => __('Sundays')],
                            ]"
                        option-value="id"
                        option-label="name"
                        wire:model="schedule.cron.parameters.basic.0"
                    />
                    <x-time-picker
                        :label="__('Time')"
                        format="24"
                        wire:model="schedule.cron.parameters.basic.1"
                    />
                </div>
                <div x-cloak x-show="['monthlyOn', 'quarterlyOn'].indexOf($wire.schedule.cron.methods.basic) >= 0" class="flex flex-col gap-4">
                    <x-inputs.number :max="31" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Day')" />
                    <x-time-picker
                        :label="__('Time')"
                        format="24"
                        wire:model="schedule.cron.parameters.basic.1"
                    />
                </div>
                <div x-cloak x-show="$wire.schedule.cron.methods.basic === 'twiceMonthly'" class="flex flex-col gap-4">
                    <x-inputs.number :max="31" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Day')" />
                    <div class="mt-4">
                        <x-inputs.number :max="31" :min="0" wire:model="schedule.cron.parameters.basic.1" :label="__('Day')" />
                    </div>
                    <x-time-picker
                        :label="__('Time')"
                        format="24"
                        wire:model="schedule.cron.parameters.basic.2"
                    />
                </div>
                <div x-cloak x-show="$wire.schedule.cron.methods.basic === 'yearlyOn'" class="flex flex-col gap-4">
                    <x-select
                        :label="__('Month')"
                        :options="[
                                ['id' => 1, 'name' => __('January'), 'days' => 31],
                                ['id' => 2, 'name' => __('February'), 'days' => 28],
                                ['id' => 3, 'name' => __('March'), 'days' => 31],
                                ['id' => 4, 'name' => __('April'), 'days' => 30],
                                ['id' => 5, 'name' => __('May'), 'days' => 31],
                                ['id' => 6, 'name' => __('June'), 'days' => 30],
                                ['id' => 7, 'name' => __('July'), 'days' => 31],
                                ['id' => 8, 'name' => __('August'), 'days' => 31],
                                ['id' => 9, 'name' => __('September'), 'days' => 30],
                                ['id' => 10, 'name' => __('October'), 'days' => 31],
                                ['id' => 11, 'name' => __('November'), 'days' => 30],
                                ['id' => 12, 'name' => __('December'), 'days' => 31],
                            ]"
                        option-value="id"
                        option-label="name"
                        wire:model="schedule.cron.parameters.basic.0"
                        x-on:selected="document.getElementById('month-day-input').max = $event.detail.days; $wire.schedule.cron.parameters.basic[1] = Math.min($wire.schedule.cron.parameters.basic[1], $event.detail.days);"
                    />
                    <x-inputs.number id="month-day-input" :max="31" :min="0" wire:model.blur="schedule.cron.parameters.basic.1" :label="__('Day')" />
                    <x-time-picker
                        :label="__('Time')"
                        format="24"
                        wire:model="schedule.cron.parameters.basic.2"
                    />
                </div>
                <x-datetime-picker wire:model="schedule.due_at" :label="__('Due At')" timezone="UTC"/>
                <x-label>
                    {{ __('End') }}
                </x-label>
                <x-radio :label="__('Never')" value="never" wire:model="schedule.end_radio" />
                <div class="grid grid-cols-2 items-center gap-1.5">
                    <x-radio :label="__('Ends At')" value="ends_at" wire:model="schedule.end_radio" />
                    <x-datetime-picker wire:model="schedule.ends_at" timezone="UTC" x-bind:disabled="$wire.schedule.end_radio !== 'ends_at'"/>
                    <x-radio :label="__('After number of recurrences')" value="recurrences" wire:model="schedule.end_radio" />
                    <x-inputs.number wire:model="schedule.recurrences" :min="1" x-bind:disabled="$wire.schedule.end_radio !== 'recurrences'" />
                </div>
                <div class="grid grid-cols-2 items-center gap-1.5 mb-2" x-cloak x-show="$wire.schedule.id && $wire.schedule.end_radio === 'recurrences'">
                    <x-label>{{ __('Current Recurrence') }}</x-label>
                    <span class="flex justify-center">{{ $schedule->current_recurrence ?? 0 }}</span>
                </div>
                <x-toggle wire:model="schedule.is_active" :label="__('Is Active')" />
            </div>
            <x-slot:footer>
                <div class="flex gap-1.5 justify-end">
                    <x-button
                        x-on:click="close()"
                        :label="__('Cancel')"
                    />
                    <x-button
                        wire:click="saveSchedule().then((success) => { if(success) close(); })"
                        primary
                        :label="__('Save')"
                    />
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
@endsection
@section('actions')
    @parent
    <x-button
        primary
        class="w-full"
        icon="clock"
        x-on:click="$openModal('edit-schedule')"
        :label="__('Schedule')"
    />
@endsection
