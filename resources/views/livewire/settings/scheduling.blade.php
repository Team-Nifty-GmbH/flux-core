<x-modal id="edit-schedule-modal" :title="__('Schedule')">
    <div class="flex flex-col gap-4">
        <div x-cloak x-show="!$wire.schedule.id">
            <x-select.styled
                :label="__('Name')"
                required
                searchable
                autocomplete="off"
                wire:model.live="schedule.name"
                select="label:name|value:id"
                :options="$repeatable"
            />
        </div>
        <div x-cloak x-show="$wire.schedule.id">
            <span x-text="$wire.schedule.name"></span>
        </div>
        <x-textarea
            wire:model="schedule.description"
            :label="__('Description')"
        />
        <template x-for="(value, parameter) in $wire.schedule.parameters">
            <div>
                <x-label>
                    <span
                        x-html="parameter"
                        x-bind:for="$wire.schedule.parameters[parameter]"
                    />
                </x-label>
                <x-input x-model="$wire.schedule.parameters[parameter]" />
            </div>
        </template>
        <x-select.styled
            :label="__('Repeat')"
            autocomplete="off"
            wire:model.live="schedule.cron.methods.basic"
            :options="$basic"
        />
        <x-flux::schedule.basic-parameters
            :method="data_get($this->schedule, 'cron.methods.basic')"
        />
        <x-select.styled
            :label="__('Day Constraints')"
            autocomplete="off"
            wire:model="schedule.cron.methods.dayConstraint"
            :options="$dayConstraints"
        />
        <div
            x-cloak
            x-show="$wire.schedule.cron.methods.dayConstraint === 'days'"
        >
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
        <div
            x-cloak
            x-show="$wire.schedule.cron.methods.timeConstraint === 'at'"
        >
            <x-time
                :label="__('Time')"
                wire:model="schedule.cron.parameters.timeConstraint.0"
            />
        </div>
        <div
            x-cloak
            x-show="
                $wire.schedule.cron.methods.timeConstraint &&
                $wire.schedule.cron.methods.timeConstraint !== 'at'
            "
            class="flex flex-col gap-4"
        >
            <x-time
                :label="__('Start')"
                wire:model="schedule.cron.parameters.timeConstraint.0"
            />
            <x-time
                :label="__('End')"
                wire:model="schedule.cron.parameters.timeConstraint.1"
            />
        </div>
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
            x-show="
                $wire.schedule.id && $wire.schedule.end_radio === 'recurrences'
            "
        >
            <x-label>{{ __('Current Recurrence') }}</x-label>
            <span class="flex justify-center">
                {{ $schedule->current_recurrence ?? 0 }}
            </span>
        </div>
        <x-toggle wire:model="schedule.is_active" :label="__('Is Active')" />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('edit-schedule-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="
                $wire.save().then((success) => {
                    if (success) $tsui.close.modal('edit-schedule-modal');
                })
            "
        />
    </x-slot:footer>
</x-modal>
