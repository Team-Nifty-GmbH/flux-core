@extends("flux::livewire.order.order")
@section("modals")
    @parent
    <x-modal id="edit-schedule" :title="__('Edit Schedule')">
        <div class="flex flex-col gap-1.5">
            <x-select.styled
                :label="__('Order type')"
                wire:model="schedule.parameters.orderTypeId"
                required
                select="label:name|value:id"
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
                <x-time
                    :label="__('Time')"
                    format="24"
                    wire:model="schedule.cron.parameters.basic.0"
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
                <x-time
                    :label="__('Time')"
                    format="24"
                    wire:model="schedule.cron.parameters.basic.1"
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
                <x-time
                    :label="__('Time')"
                    format="24"
                    wire:model="schedule.cron.parameters.basic.1"
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
                <x-time
                    :label="__('Time')"
                    format="24"
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
                <x-time
                    :label="__('Time')"
                    format="24"
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
                :label="__('Never')"
                value="never"
                wire:model="schedule.end_radio"
            />
            <div class="grid grid-cols-2 items-center gap-1.5">
                <x-radio
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

@section("actions")
    @parent
    <x-button
        color="indigo"
        class="w-full"
        icon="clock"
        x-on:click="$modalOpen('edit-schedule')"
        :text="__('Schedule')"
    />
@endsection
