<div>
    <x-modal name="edit-schedule">
        <x-card>
            <div class="flex flex-col gap-4">
                <div x-show="! $wire.schedule.id">
                    <x-select
                        :label="__('Name')"
                        :options="$repeatable"
                        option-value="id"
                        option-label="name"
                        option-description="description"
                        :clearable="false"
                        autocomplete="off"
                        wire:model.live="schedule.name"
                    />
                </div>
                <div x-show="$wire.schedule.id">
                    <span x-text="$wire.schedule.name"></span>
                </div>
                <x-textarea wire:model="schedule.description" :label="__('Description')" />
                <template x-for="(value, parameter) in $wire.schedule.parameters">
                    <div>
                        <x-label x-html="parameter" x-bind:for="$wire.schedule.parameters[parameter]" />
                        <x-input x-model="$wire.schedule.parameters[parameter]"/>
                    </div>
                </template>
                <x-select
                    :label="__('Repeat')"
                    :options="$basic"
                    option-value="name"
                    option-label="label"
                    autocomplete="off"
                    wire:model.live="schedule.cron.methods.basic"
                />
                <div x-show="[
                        'hourlyAt',
                        'everyOddHour',
                        'everyTwoHours',
                        'everyThreeHours',
                        'everyFourHours',
                        'everySixHours'
                    ].indexOf($wire.schedule.cron.methods.basic) >= 0
                ">
                    <x-inputs.number :max="59" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Minute')" />
                </div>
                <div x-show="['dailyAt', 'lastDayOfMonth'].indexOf($wire.schedule.cron.methods.basic) >= 0">
                    <x-time-picker
                        :label="__('Time')"
                        format="24"
                        wire:model="schedule.cron.parameters.basic.0"
                    />
                </div>
                <div x-show="$wire.schedule.cron.methods.basic === 'twiceDaily'">
                    <x-inputs.number :max="23" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Hour')" />
                    <div class="mt-4">
                        <x-inputs.number :max="23" :min="0" wire:model="schedule.cron.parameters.basic.1" :label="__('Hour')" />
                    </div>
                </div>
                <div x-show="$wire.schedule.cron.methods.basic === 'twiceDailyAt'">
                    <x-inputs.number :max="23" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Hour')" />
                    <div class="mt-4">
                        <x-inputs.number :max="23" :min="0" wire:model="schedule.cron.parameters.basic.1" :label="__('Hour')" />
                    </div>
                    <div class="mt-4">
                        <x-inputs.number :max="59" :min="0" wire:model="schedule.cron.parameters.basic.2" :label="__('Minute')" />
                    </div>
                </div>
                <div x-show="$wire.schedule.cron.methods.basic === 'weeklyOn'">
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
                    <div class="mt-4">
                        <x-time-picker
                            :label="__('Time')"
                            format="24"
                            wire:model="schedule.cron.parameters.basic.1"
                        />
                    </div>
                </div>
                <div x-show="['monthlyOn', 'quarterlyOn'].indexOf($wire.schedule.cron.methods.basic) >= 0">
                    <x-inputs.number :max="31" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Day')" />
                    <div class="mt-4">
                        <x-time-picker
                            :label="__('Time')"
                            format="24"
                            wire:model="schedule.cron.parameters.basic.1"
                        />
                    </div>
                </div>
                <div x-show="$wire.schedule.cron.methods.basic === 'twiceMonthly'">
                    <x-inputs.number :max="31" :min="0" wire:model="schedule.cron.parameters.basic.0" :label="__('Day')" />
                    <div class="mt-4">
                        <x-inputs.number :max="31" :min="0" wire:model="schedule.cron.parameters.basic.1" :label="__('Day')" />
                    </div>
                    <div class="mt-4">
                        <x-time-picker
                            :label="__('Time')"
                            format="24"
                            wire:model="schedule.cron.parameters.basic.2"
                        />
                    </div>
                </div>
                <div x-show="$wire.schedule.cron.methods.basic === 'yearlyOn'">
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
                    <div class="mt-4">
                        <x-inputs.number id="month-day-input" :max="31" :min="0" wire:model.blur="schedule.cron.parameters.basic.1" :label="__('Day')" />
                    </div>
                    <div class="mt-4">
                        <x-time-picker
                            :label="__('Time')"
                            format="24"
                            wire:model="schedule.cron.parameters.basic.2"
                        />
                    </div>
                </div>
                <x-select
                    :label="__('Day Constraints')"
                    :options="$dayConstraints"
                    option-value="name"
                    option-label="label"
                    autocomplete="off"
                    wire:model.live="schedule.cron.methods.dayConstraint"
                />
                <div x-show="$wire.schedule.cron.methods.dayConstraint === 'days'">
                    <x-select
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
                        :multiselect="true"
                        wire:model="schedule.cron.parameters.dayConstraint"
                    />
                </div>
                <x-select
                    :label="__('Time Constraints')"
                    :options="$timeConstraints"
                    option-value="name"
                    option-label="label"
                    autocomplete="off"
                    wire:model.live="schedule.cron.methods.timeConstraint"
                />
                <div x-show="$wire.schedule.cron.methods.timeConstraint === 'at'">
                    <x-time-picker
                        :label="__('Time')"
                        format="24"
                        wire:model="schedule.cron.parameters.timeConstraint.0"
                    />
                </div>
                <div x-show="$wire.schedule.cron.methods.timeConstraint && $wire.schedule.cron.methods.timeConstraint !== 'at'">
                    <x-time-picker
                        :label="__('Start')"
                        format="24"
                        wire:model="schedule.cron.parameters.timeConstraint.0"
                    />
                    <div class="mt-4">
                        <x-time-picker
                            :label="__('End')"
                            format="24"
                            wire:model="schedule.cron.parameters.timeConstraint.1"
                        />
                    </div>
                </div>
                <x-toggle wire:model="schedule.is_active" :label="__('Is Active')" />
            </div>
            <x-slot:footer>
                <div class="flex justify-between gap-x-4">
                    @if(\FluxErp\Actions\Schedule\DeleteSchedule::canPerformAction(false))
                        <div x-bind:class="$wire.schedule.id > 0 || 'invisible'">
                            <x-button
                                flat
                                negative
                                :label="__('Delete')"
                                x-on:click="close"
                                wire:click="delete().then((success) => { if(success) close()})"
                                wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Schedule')]) }}"
                            />
                        </div>
                    @endif
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
