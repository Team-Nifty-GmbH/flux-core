<div class="mx-auto md:flex md:items-center md:justify-end md:space-x-5">
    <div
        class="flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-x-3 sm:space-y-0 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3"
    >
        <x-button
            color="indigo"
            x-cloak
            x-show="!edit"
            class="w-full"
            x-on:click="edit = true"
            :text="__('Edit')"
        />
        <x-button
            x-cloak
            color="indigo"
            loading="save"
            x-cloak
            x-show="edit"
            class="w-full"
            x-on:click="$wire.save().then((success) => {
                edit = false;
            });"
            :text="__('Save')"
        />
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-cloak
            loading="save"
            x-show="edit"
            class="w-full"
            x-on:click="edit = false; $wire.resetForm();"
        />
    </div>
</div>
<div class="space-y-6 pb-16 pt-4">
    <x-card :header="__('Basic Information')">
        <div class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <x-input
                :label="__('Firstname')"
                wire:model="employee.firstname"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Lastname')"
                wire:model="employee.lastname"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Email')"
                type="email"
                wire:model="employee.email"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Employee Number')"
                wire:model="employee.employee_number"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Phone')"
                wire:model="employee.phone"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Phone Mobile')"
                wire:model="employee.mobile_phone"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('IBAN')"
                wire:model="employee.iban"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Account Holder')"
                wire:model="employee.account_holder"
                x-bind:disabled="!edit"
            />
        </div>
    </x-card>

    <x-card :header="__('Personal Information')">
        <div class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <x-date
                :label="__('Date Of Birth')"
                wire:model="employee.date_of_birth"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Place Of Birth')"
                wire:model="employee.place_of_birth"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Nationality')"
                wire:model="employee.nationality"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Confession')"
                wire:model="employee.confession"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Street')"
                wire:model="employee.street"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Zip')"
                wire:model="employee.zip"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('City')"
                wire:model="employee.city"
                x-bind:disabled="!edit"
            />
            <div x-bind:class="!edit && 'pointer-events-none'">
                <x-number
                    min="0"
                    step="0.5"
                    :label="__('Number Of Children')"
                    wire:model="employee.number_of_children"
                />
            </div>
        </div>
    </x-card>

    <x-card :header="__('Government & Insurance')">
        <div class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <x-input
                :label="__('Social Security Number')"
                wire:model="employee.social_security_number"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Tax Identification Number')"
                wire:model="employee.tax_identification_number"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Health Insurance')"
                wire:model="employee.health_insurance"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Health Insurance Member Number')"
                wire:model="employee.health_insurance_member_number"
                x-bind:disabled="!edit"
            />
        </div>
    </x-card>

    {{-- Employment Information --}}
    <x-card :header="__('Employment Information')">
        <div
            class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2"
            x-bind:class="!edit && 'pointer-events-none'"
        >
            <x-date
                :label="__('Employment Date')"
                wire:model="employee.employment_date"
                x-bind:disabled="!edit"
            />
            <x-date
                :label="__('Termination Date')"
                wire:model="employee.termination_date"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Job Title')"
                wire:model="employee.job_title"
                x-bind:disabled="!edit"
            />
            <x-date
                :label="__('Probation Period Until')"
                wire:model="employee.probation_period_until"
                x-bind:disabled="!edit"
            />
            <x-date
                :label="__('Fixed Term Contract Until')"
                wire:model="employee.fixed_term_contract_until"
                x-bind:disabled="!edit"
            />
            <x-date
                :label="__('Work Permit Until')"
                wire:model="employee.work_permit_until"
                x-bind:disabled="!edit"
            />
            <x-date
                :label="__('Residence Permit Until')"
                wire:model="employee.residence_permit_until"
                x-bind:disabled="!edit"
            />
            <x-select.styled
                :label="__('Client')"
                wire:model="employee.client_id"
                select="label:name|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Client::class),
                    'method' => 'POST',
                ]"
            />
            <x-select.styled
                :label="__('Department')"
                wire:model="employee.employee_department_id"
                select="label:name|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\EmployeeDepartment::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name']
                    ]
                ]"
            />
            <x-select.styled
                :label="__('Location')"
                wire:model="employee.location_id"
                select="label:name|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Location::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name'],
                    ]
                ]"
            />
        </div>
    </x-card>

    <x-card :header="__('Work Time Model')">
        <livewire:employee.employee-work-time-model-assignment
            :employeeId="$this->employee->id"
        />
    </x-card>

    <x-card :header="__('Vacation Carryover Rule')">
        <div class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div x-bind:class="!edit && 'pointer-events-none'">
                <x-select.styled
                    :label="__('Vacation Carryover Rule')"
                    wire:model="employee.vacation_carryover_rule_id"
                    required
                    select="label:name|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\VacationCarryoverRule::class),
                        'method' => 'POST',
                        'params' => [
                            'searchFields' => ['name'],
                            'where' => [
                                [
                                    'is_active',
                                    '=',
                                    true,
                                ],
                            ],
                        ],
                    ]"
                />
            </div>
        </div>
    </x-card>
</div>
