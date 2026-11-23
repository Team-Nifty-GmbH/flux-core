@props([
    'frequency' => null,
    'preview' => false,
])

@php($onChange = $preview ? '$wire.previewSchedule()' : '')

@if (in_array($frequency, ['hourlyAt', 'everyOddHour', 'everyTwoHours', 'everyThreeHours', 'everyFourHours', 'everySixHours']))
    <div>
        <x-number
            :max="59"
            :min="0"
            wire:model="schedule.cron.parameters.basic.0"
            x-on:change="{{ $onChange }}"
            :label="__('Minute')"
        />
    </div>
@endif

@if (in_array($frequency, ['dailyAt', 'lastDayOfMonth']))
    <div>
        <x-flux::schedule.time
            model="schedule.cron.parameters.basic.0"
            :preview="$preview"
        />
    </div>
@endif

@if (in_array($frequency, ['twiceDaily', 'twiceDailyAt']))
    <div class="flex flex-col gap-4">
        <x-number
            :max="23"
            :min="0"
            wire:model="schedule.cron.parameters.basic.0"
            x-on:change="{{ $onChange }}"
            :label="__('Hour')"
        />
        <x-number
            :max="23"
            :min="0"
            wire:model="schedule.cron.parameters.basic.1"
            x-on:change="{{ $onChange }}"
            :label="__('Hour')"
        />
        @if ($frequency === 'twiceDailyAt')
            <x-number
                :max="59"
                :min="0"
                wire:model="schedule.cron.parameters.basic.2"
                x-on:change="{{ $onChange }}"
                :label="__('Minute')"
            />
        @endif
    </div>
@endif

@if ($frequency === 'weeklyOn')
    <div class="flex flex-col gap-4">
        <x-select.styled
            :label="__('Weekday')"
            wire:model="schedule.cron.parameters.basic.0"
            x-on:select="{{ $onChange }}"
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
        <x-flux::schedule.time
            model="schedule.cron.parameters.basic.1"
            :preview="$preview"
        />
    </div>
@endif

@if (in_array($frequency, ['monthlyOn', 'quarterlyOn']))
    <div class="flex flex-col gap-4">
        <x-number
            :max="31"
            :min="0"
            wire:model="schedule.cron.parameters.basic.0"
            x-on:change="{{ $onChange }}"
            :label="__('Day')"
        />
        <x-flux::schedule.time
            model="schedule.cron.parameters.basic.1"
            :preview="$preview"
        />
    </div>
@endif

@if ($frequency === 'twiceMonthly')
    <div class="flex flex-col gap-4">
        <x-number
            :max="31"
            :min="0"
            wire:model="schedule.cron.parameters.basic.0"
            x-on:change="{{ $onChange }}"
            :label="__('Day')"
        />
        <div class="mt-4">
            <x-number
                :max="31"
                :min="0"
                wire:model="schedule.cron.parameters.basic.1"
                x-on:change="{{ $onChange }}"
                :label="__('Day')"
            />
        </div>
        <x-flux::schedule.time
            model="schedule.cron.parameters.basic.2"
            :preview="$preview"
        />
    </div>
@endif

@if ($frequency === 'yearlyOn')
    <div class="flex flex-col gap-4">
        <x-select.styled
            :label="__('Month')"
            wire:model="schedule.cron.parameters.basic.0"
            x-on:select="
                document.getElementById('month-day-input').max =
                    $event.detail.select.days;
                $wire.schedule.cron.parameters.basic[1] = Math.min(
                    $wire.schedule.cron.parameters.basic[1],
                    $event.detail.select.days,
                );
                {{ $onChange }}
            "
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
            x-on:change="{{ $onChange }}"
            :label="__('Day')"
        />
        <x-flux::schedule.time
            model="schedule.cron.parameters.basic.2"
            :preview="$preview"
        />
    </div>
@endif
