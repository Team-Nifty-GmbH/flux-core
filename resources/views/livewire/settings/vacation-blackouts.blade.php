<div>
    <x-modal :id="$vacationBlackoutForm->modalName()" :title="__('Vacation Blackout')">
        <div class="flex flex-col gap-4">
            <x-input
                wire:model="vacationBlackoutForm.name"
                :label="__('Name')"
                required
            />

            <div class="grid grid-cols-2 gap-4">
                <x-date
                    wire:model="vacationBlackoutForm.start_date"
                    :label="__('Start Date')"
                    required
                />

                <x-date
                    wire:model="vacationBlackoutForm.end_date"
                    :label="__('End Date')"
                    required
                />
            </div>

            <x-textarea
                wire:model="vacationBlackoutForm.description"
                :label="__('Description')"
                rows="3"
            />

            <x-select.styled
                wire:model="vacationBlackoutForm.employees"
                :label="__('Applies to Employees')"
                :placeholder="__('Select Employees')"
                :hint="__('Select specific employees for this blackout period')"
                multiple
                select="label:label|value:id"
                :request="[
                    'url' => route('search', \FluxErp\Models\Employee::class),
                    'method' => 'POST'
                ]"
                unfiltered
            />

            <x-select.styled
                wire:model="vacationBlackoutForm.employee_departments"
                :label="__('Applies to Departments')"
                :placeholder="__('Select Departments')"
                :hint="__('Select departments for this blackout period')"
                multiple
                select="label:name|value:id"
                :request="[
                    'url' => route('search', \FluxErp\Models\EmployeeDepartment::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name'],
                    ],
                ]"
                unfiltered
            />

            <x-select.styled
                wire:model="vacationBlackoutForm.locations"
                :label="__('Applies to Locations')"
                :placeholder="__('Select Locations')"
                :hint="__('Select locations for this blackout period')"
                multiple
                select="label:name|value:id"
                :request="[
                    'url' => route('search', \FluxErp\Models\Location::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name'],
                    ],
                ]"
                unfiltered
            />

            <x-toggle
                wire:model="vacationBlackoutForm.is_active"
                :label="__('Is Active')"
            />
        </div>

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('{{ $vacationBlackoutForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="save().then((success) => {
                    if (success) {
                        $modalClose('{{ $vacationBlackoutForm->modalName() }}');
                    }
                })"
            />
        </x-slot:footer>
    </x-modal>
</div>
