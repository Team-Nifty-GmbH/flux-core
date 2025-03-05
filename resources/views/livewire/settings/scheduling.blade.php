<x-modal id="edit-schedule-modal">
    <div class="flex flex-col gap-4">
        <div x-cloak x-show="! $wire.schedule.id">
            <x-select.styled
                :label="__('Name')"
                required
                autocomplete="off"
                wire:model.live="schedule.name"
                select="label:name|value:id"
                :options="$repeatable"
            />
        </div>
        <div x-cloak x-show="$wire.schedule.id">
            <span x-text="$wire.schedule.name"></span>
        </div>
        <x-textarea wire:model="schedule.description" :label="__('Description')" />
        <template x-for="(value, parameter) in $wire.schedule.parameters">
            <div>
                <x-label x-html="parameter" x-bind:for="$wire.schedule.parameters[parameter]" />
                <x-input x-model="$wire.schedule.parameters[parameter]"/>
            </div>
        </template>
        <x-select.styled
            :label="__('Repeat')"
            autocomplete="off"
            wire:model="schedule.cron.methods.basic"
            :options="$basic"
        />
        <div
            x-cloak
            x-show="[
                'hourlyAt',
                'everyOddHour',
                'everyTwoHours',
                'everyThreeHours',
                'everyFourHours',
                'everySixHours'
            ].indexOf($wire.schedule.cron.methods.basic) >= 0"
        >
            <x-number :max="59" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Minute')" />
        </div>
        <div x-cloak x-show="['dailyAt', 'lastDayOfMonth'].indexOf($wire.schedule.cron.methods.basic) >= 0">
            <x-time
                :label="__('Time')"
                format="24"
                wire:model="schedule.cron.parameters.basic.0"
            />
        </div>
        <div x-cloak x-show="$wire.schedule.cron.methods.basic === 'twiceDaily'" class="flex flex-col gap-4">
            <x-number :max="23" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Hour')" />
            <x-number :max="23" :min="0" wire:model="schedule.cron.parameters.basic.1" :label="__('Hour')" />
        </div>
        <div x-cloak x-show="$wire.schedule.cron.methods.basic === 'twiceDailyAt'" class="flex flex-col gap-4">
            <x-number :max="23" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Hour')" />
            <x-number :max="23" :min="0" wire:model="schedule.cron.parameters.basic.1" :label="__('Hour')" />
            <x-number :max="59" :min="0" wire:model="schedule.cron.parameters.basic.2" :label="__('Minute')" />
        </div>
        <div x-cloak x-show="$wire.schedule.cron.methods.basic === 'weeklyOn'" class="flex flex-col gap-4">
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
        <div x-cloak x-show="['monthlyOn', 'quarterlyOn'].indexOf($wire.schedule.cron.methods.basic) >= 0" class="flex flex-col gap-4">
            <x-number :max="31" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Day')" />
            <x-time
                :label="__('Time')"
                format="24"
                wire:model="schedule.cron.parameters.basic.1"
            />
        </div>
        <div x-cloak x-show="$wire.schedule.cron.methods.basic === 'twiceMonthly'" class="flex flex-col gap-4">
            <x-number :max="31" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Day')" />
            <div class="mt-4">
                <x-number :max="31" :min="0" wire:model="schedule.cron.parameters.basic.1" :label="__('Day')" />
            </div>
            <x-time
                :label="__('Time')"
                format="24"
                wire:model="schedule.cron.parameters.basic.2"
            />
        </div>
        <div x-cloak x-show="$wire.schedule.cron.methods.basic === 'yearlyOn'" class="flex flex-col gap-1.5">
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
            <x-number id="month-day-input" :max="31" :min="0" wire:model.blur="schedule.cron.parameters.basic.1" :label="__('Day')" />
            <x-time
                :label="__('Time')"
                format="24"
                wire:model="schedule.cron.parameters.basic.2"
            />
        </div>
        <x-select.styled
            :label="__('Day Constraints')"
            autocomplete="off"
            wire:model="schedule.cron.methods.dayConstraint"
            :options="$dayConstraints"
        />
        <div x-cloak x-show="$wire.schedule.cron.methods.dayConstraint === 'days'">
            <x-select.styled
                multiple
                wire:model="schedule.cron.parameters.dayConstraint"
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
        </div>
        <x-select.styled
            :label="__('Time Constraints')"
            autocomplete="off"
            wire:model="schedule.cron.methods.timeConstraint"
            :options="$timeConstraints"
        />
        <div x-cloak x-show="$wire.schedule.cron.methods.timeConstraint === 'at'">
            <x-time
                :label="__('Time')"
                format="24"
                wire:model="schedule.cron.parameters.timeConstraint.0"
            />
        </div>
        <div x-cloak x-show="$wire.schedule.cron.methods.timeConstraint && $wire.schedule.cron.methods.timeConstraint !== 'at'" class="flex flex-col gap-4">
            <x-time
                :label="__('Start')"
                format="24"
                wire:model="schedule.cron.parameters.timeConstraint.0"
            />
            <x-time
                :label="__('End')"
                format="24"
                wire:model="schedule.cron.parameters.timeConstraint.1"
            />
        </div>
        <x-label :label="__('End')" />
        <x-radio :label="__('Never')" value="never" wire:model="schedule.end_radio" />
        <div class="grid grid-cols-2 items-center gap-1.5">
            <x-radio :label="__('Ends At')" value="ends_at" wire:model="schedule.end_radio" />
            <x-date wire:model="schedule.ends_at" timezone="UTC" x-bind:disabled="$wire.schedule.end_radio !== 'ends_at'"/>
            <x-radio :label="__('After number of recurrences')" value="recurrences" wire:model="schedule.end_radio" />
            <x-number wire:model="schedule.recurrences" :min="1" x-bind:disabled="$wire.schedule.end_radio !== 'recurrences'" />
        </div>
        <div class="grid grid-cols-2 items-center gap-1.5 mb-2" x-cloak x-show="$wire.schedule.id && $wire.schedule.end_radio === 'recurrences'">
            <x-label>{{ __('Current Recurrence') }}</x-label>
            <span class="flex justify-center">{{ $schedule->current_recurrence ?? 0 }}</span>
        </div>
        <x-toggle wire:model="schedule.is_active" :label="__('Is Active')" />
    </div>
    <x-slot:footer>
        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('edit-schedule-modal')"/>
        <x-button color="indigo" :text="__('Save')" wire:click="save().then((success) => { if(success) $modalClose('edit-schedule-modal')})"/>
    </x-slot:footer>
</x-modal>
