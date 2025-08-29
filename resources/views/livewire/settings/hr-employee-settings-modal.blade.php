<div>
    <x-modal :id="$employeeForm->modalName()" size="3xl">
        <x-slot:title>
            {{ __('Edit HR Settings') }}
        </x-slot:title>

        <div class="flex flex-col gap-4">
            <div class="border rounded-lg p-4">
                <h3 class="font-medium mb-3">{{ __('Employment Information') }}</h3>
                <div class="grid grid-cols-2 gap-4">
                    <x-date
                        wire:model="employeeForm.employment_date"
                        :label="__('Employment Date')"
                    />

                    <x-date
                        wire:model="employeeForm.termination_date"
                        :label="__('Termination Date')"
                    />

                    <x-input
                        wire:model="employeeForm.employee_number"
                        :label="__('Employee Number')"
                    />

                    <x-select.styled
                        wire:model="employeeForm.employee_department_id"
                        :label="__('Department')"
                        select="label:name|value:id"
                        :request="[
                            'url' => route('search', \FluxErp\Models\EmployeeDepartment::class),
                            'method' => 'POST',
                            'params' => [
                                'searchFields' => ['name', 'code']
                            ]
                        ]"
                        unfiltered
                    />
                </div>
            </div>

            <div class="border rounded-lg p-4">
                <h3 class="font-medium mb-3">{{ __('Work Time & Location') }}</h3>
                <div class="grid grid-cols-2 gap-4">
                    <x-select.styled
                        wire:model="employeeForm.work_time_model_id"
                        :label="__('Work Time Model')"
                        select="label:name|value:id"
                        :request="[
                            'url' => route('search', \FluxErp\Models\WorkTimeModel::class),
                            'method' => 'POST',
                            'params' => [
                                'searchFields' => ['name']
                            ]
                        ]"
                        unfiltered
                    />

                    <x-select.styled
                        wire:model="employeeForm.location_id"
                        :label="__('Location')"
                        select="label:name|value:id"
                        :request="[
                            'url' => route('search', \FluxErp\Models\Location::class),
                            'method' => 'POST',
                            'params' => [
                                'searchFields' => ['name']
                            ]
                        ]"
                        unfiltered
                    />

                    <x-select.styled
                        wire:model="employeeForm.supervisor_id"
                        :label="__('Supervisor')"
                        select="label:name|value:id"
                        :request="[
                            'url' => route('search', \FluxErp\Models\Employee::class),
                            'method' => 'POST',
                            'params' => [
                                'searchFields' => ['name', 'email']
                            ]
                        ]"
                        unfiltered
                    />
                </div>
            </div>

            <div class="border rounded-lg p-4">
                <h3 class="font-medium mb-3">{{ __('Vacation Settings') }}</h3>
                <div class="grid grid-cols-2 gap-4">
                    <x-number
                        wire:model="employeeForm.yearly_vacation_days"
                        :label="__('Yearly Vacation Days')"
                        min="0"
                        step="0.5"
                    />

                    <x-number
                        wire:model="employeeForm.additional_vacation_days"
                        :label="__('Additional Vacation Days')"
                        min="0"
                        step="0.5"
                    />

                    <x-number
                        wire:model="employeeForm.previous_year_vacation_days"
                        :label="__('Previous Year Vacation Days')"
                        min="0"
                        step="0.5"
                    />

                    <x-number
                        wire:model="employeeForm.vacation_days_current"
                        :label="__('Current Vacation Days')"
                        step="0.5"
                    />
                </div>
            </div>

            <div class="border rounded-lg p-4">
                <h3 class="font-medium mb-3">{{ __('Salary Information') }}</h3>
                <div class="grid grid-cols-2 gap-4">
                    <x-number
                        wire:model="employeeForm.salary"
                        :label="__('Salary')"
                        min="0"
                        step="0.01"
                    />
                </div>
            </div>
        </div>

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('{{ $employeeForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="save"
            />
        </x-slot:footer>
    </x-modal>
</div>
