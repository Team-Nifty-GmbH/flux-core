@extends('flux::livewire.order.order')
@section('modals')
    @parent
    <x-modal id="edit-schedule" :title="__('Edit Schedule')" persistent>
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                :label="__('Order type')"
                wire:model.live="schedule.parameters.orderTypeId"
                required
                select="label:name|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\OrderType::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => [
                            'name',
                        ],
                        'select' => [
                            'name',
                            'id',
                        ],
                        'where' => [
                            [
                                'is_active',
                                '=',
                                true,
                            ],
                            [
                                'is_hidden',
                                '=',
                                false,
                            ],
                        ],
                    ],
                ]"
            />
            <x-select.styled
                :label="__('Repeat')"
                autocomplete="off"
                required
                wire:model="schedule.cron.methods.basic"
                :options="$frequencies"
            />
            <div
                x-cloak
                x-show="['dailyAt', 'lastDayOfMonth'].indexOf($wire.schedule.cron.methods.basic) >= 0"
            >
                <x-input
                    type="time"
                    :label="__('Time')"
                    wire:model.live="schedule.cron.parameters.basic.0"
                />
            </div>
            <div
                x-cloak
                x-show="$wire.schedule.cron.methods.basic === 'weeklyOn'"
                class="flex flex-col gap-4"
            >
                <x-select.styled
                    :label="__('Weekday')"
                    wire:model="schedule.cron.parameters.basic.0"
                    select="label:name|value:id"
                    :options="[
                        ['id' => 1, 'name' => __('Mondays')],
                        ['id' => 2, 'name' => __('Tuesdays')],
                        ['id' => 3, 'name' => __('Wednesdays')],
                        ['id' => 4, 'name' => __('Thursdays')],
                        ['id' => 5, 'name' => __('Fridays')],
                        ['id' => 6, 'name' => __('Saturdays')],
                        ['id' => 0, 'name' => __('Sundays')],
                    ]"
                />
                <x-input
                    type="time"
                    :label="__('Time')"
                    wire:model.live="schedule.cron.parameters.basic.1"
                />
            </div>
            <div
                x-cloak
                x-show="['monthlyOn', 'quarterlyOn'].indexOf($wire.schedule.cron.methods.basic) >= 0"
                class="flex flex-col gap-4"
            >
                <x-number
                    :max="31"
                    :min="0"
                    wire:model="schedule.cron.parameters.basic.0"
                    :label="__('Day')"
                />
                <x-input
                    type="time"
                    :label="__('Time')"
                    wire:model.live="schedule.cron.parameters.basic.1"
                />
            </div>
            <div
                x-cloak
                x-show="$wire.schedule.cron.methods.basic === 'twiceMonthly'"
                class="flex flex-col gap-4"
            >
                <x-number
                    :max="31"
                    :min="0"
                    wire:model="schedule.cron.parameters.basic.0"
                    :label="__('Day')"
                />
                <div class="mt-4">
                    <x-number
                        :max="31"
                        :min="0"
                        wire:model="schedule.cron.parameters.basic.1"
                        :label="__('Day')"
                    />
                </div>
                <x-input
                    type="time"
                    :label="__('Time')"
                    wire:model="schedule.cron.parameters.basic.2"
                />
            </div>
            <div
                x-cloak
                x-show="$wire.schedule.cron.methods.basic === 'yearlyOn'"
                class="flex flex-col gap-4"
            >
                <x-select.styled
                    :label="__('Month')"
                    wire:model="schedule.cron.parameters.basic.0"
                    x-on:select="document.getElementById('month-day-input').max = $event.detail.select.days; $wire.schedule.cron.parameters.basic[1] = Math.min($wire.schedule.cron.parameters.basic[1], $event.detail.select.days);"
                    select="label:name|value:id"
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
                />
                <x-number
                    id="month-day-input"
                    :max="31"
                    :min="0"
                    wire:model.blur="schedule.cron.parameters.basic.1"
                    :label="__('Day')"
                />
                <x-input
                    type="time"
                    :label="__('Time')"
                    wire:model="schedule.cron.parameters.basic.2"
                />
            </div>
            <x-date
                wire:model="schedule.due_at"
                :label="__('Due At')"
                timezone="UTC"
            />
            <x-label :label="__('End')" />
            <x-radio
                id="schedule-end-never-radio"
                name="schedule-end-radio"
                :label="__('Never')"
                value="never"
                wire:model="schedule.end_radio"
            />
            <div class="grid grid-cols-2 items-center gap-1.5">
                <x-radio
                    id="schedule-end-date-radio"
                    name="schedule-end-radio"
                    :label="__('Ends At')"
                    value="ends_at"
                    wire:model="schedule.end_radio"
                />
                <x-date
                    wire:model="schedule.ends_at"
                    timezone="UTC"
                    x-bind:disabled="$wire.schedule.end_radio !== 'ends_at'"
                />
                <x-radio
                    id="schedule-end-recurrences-radio"
                    name="schedule-end-radio"
                    :label="__('After number of recurrences')"
                    value="recurrences"
                    wire:model="schedule.end_radio"
                />
                <x-number
                    wire:model="schedule.recurrences"
                    :min="1"
                    x-bind:disabled="$wire.schedule.end_radio !== 'recurrences'"
                />
            </div>
            <div
                class="mb-2 grid grid-cols-2 items-center gap-1.5"
                x-cloak
                x-show="$wire.schedule.id && $wire.schedule.end_radio === 'recurrences'"
            >
                <x-label :label="__('Current Recurrence')" />
                <span class="flex justify-center">
                    {{ $schedule->current_recurrence ?? 0 }}
                </span>
            </div>
            <x-toggle
                wire:model="schedule.is_active"
                :label="__('Is Active')"
            />

            <div class="flex flex-col gap-1.5 border-t pt-4">
                <x-toggle
                    wire:model="schedule.parameters.autoPrintAndSend"
                    :label="__('Auto Print and send by mail')"
                    :hint="__('Automatically generate PDFs and send them via email')"
                />

                <div
                    x-cloak
                    x-show="$wire.schedule.parameters.autoPrintAndSend"
                    class="flex flex-col gap-1.5"
                >
                    <div id="schedule-print-layouts">
                        <x-select.styled
                            wire:model="schedule.parameters.printLayouts"
                            :label="__('Print Layouts')"
                            :hint="__('Select one or more print layouts to generate when the schedule runs')"
                            select="label:label|value:value"
                            multiple
                            :options="$this->getPrintLayoutOptions()"
                        />
                    </div>
                    <x-select.styled
                        wire:model="schedule.parameters.emailTemplateId"
                        :label="__('Email Template')"
                        :hint="__('Select the email template to use for sending')"
                        select="value:id"
                        unfiltered
                        :request="[
                            'url' => route('search', \FluxErp\Models\EmailTemplate::class),
                            'method' => 'POST',
                            'params' => [
                                'searchFields' => ['name', 'subject'],
                                'where' => [
                                    ['model_type', '=', morph_alias(\FluxErp\Models\Order::class)],
                                ],
                            ],
                        ]"
                    />
                </div>
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                x-on:click="$modalClose('edit-schedule')"
                :text="__('Cancel')"
            />
            <x-button
                color="indigo"
                wire:click="saveSchedule().then((success) => { if(success) $modalClose('edit-schedule'); })"
                primary
                :text="__('Save')"
            />
        </x-slot>
    </x-modal>
@endsection

@section('actions')
    @parent
    <x-button
        color="indigo"
        class="w-full"
        icon="clock"
        x-on:click="$modalOpen('edit-schedule')"
        :text="__('Schedule')"
    />
@endsection
