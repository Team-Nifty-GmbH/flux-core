<div>
    <x-modal
        :id="$workTimeModelForm->modalName()"
        size="xl"
        :title="__('Work Time Model')"
    >
        <div class="flex flex-col gap-4">
            <x-input
                wire:model="workTimeModelForm.name"
                :label="__('Name')"
                required
            />

            <div class="grid grid-cols-2 gap-4">
                <x-number
                    wire:model="workTimeModelForm.weekly_hours"
                    :label="__('Weekly Hours')"
                    min="0"
                    max="168"
                    step="0.5"
                    :hint="__('Total hours per week')"
                    required
                />

                <x-number
                    wire:model="workTimeModelForm.annual_vacation_days"
                    :label="__('Annual Vacation Days')"
                    min="0"
                    max="365"
                    :hint="__('Default vacation days per year')"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-number
                    wire:model="workTimeModelForm.max_overtime_hours"
                    :label="__('Max Overtime Hours')"
                    min="0"
                    step="0.5"
                    :hint="__('Maximum overtime hours allowed')"
                />

                <x-select.styled
                    wire:model="workTimeModelForm.overtime_compensation"
                    :label="__('Overtime Compensation')"
                    required
                    select="label:label|value:value"
                    :options="\FluxErp\Enums\OvertimeCompensationEnum::valuesLocalized()"
                />
            </div>

            <x-number
                wire:model="workTimeModelForm.cycle_weeks"
                :label="__('Cycle Weeks')"
                :hint="__('Number of weeks before the schedule repeats')"
                min="1"
                max="52"
            />

            <x-toggle
                wire:model="workTimeModelForm.is_active"
                :label="__('Is Active')"
            />

            <x-alert color="info">
                {{ __('After creating the work time model, you will be redirected to configure the detailed schedule.') }}
            </x-alert>
        </div>

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('{{ $workTimeModelForm->modalName() }}')"
            />
            <x-button
                :text="__('Create and Configure Schedule')"
                color="primary"
                wire:click="save().then((success) => {if(success) $wire.editSchedule($wire.workTimeModelForm.id);})"
            />
        </x-slot>
    </x-modal>
</div>
