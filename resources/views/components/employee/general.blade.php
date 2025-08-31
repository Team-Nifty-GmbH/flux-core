<div class="space-y-6 pb-16">
    <x-card :header="__('Basic Information')">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
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
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
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
            <x-number
                min="0"
                step="0.5"
                :label="__('Number Of Children')"
                wire:model="employee.number_of_children"
                x-bind:disabled="!edit"
            />
        </div>
    </x-card>

    <x-card :header="__('Contact Information')">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
            <x-input
                :label="__('Mobile Phone')"
                wire:model="employee.mobile_phone"
                x-bind:disabled="!edit"
            />
        </div>
    </x-card>

    <x-card :header="__('Government & Insurance')">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
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
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
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
                x-bind:readonly="!edit"
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
                :request="[
                    'url' => route('search', \FluxErp\Models\EmployeeDepartment::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name']
                    ]
                ]"
                select="label:label|value:id"
                unfiltered
                x-bind:disabled="!edit"
            />
            <x-select.styled
                :label="__('Location')"
                wire:model="employee.location_id"
                :request="[
                    'url' => route('search', \FluxErp\Models\Location::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name']
                    ]
                ]"
                select="label:name|value:id"
                unfiltered
                x-bind:disabled="!edit"
            />
        </div>
    </x-card>

    <x-card :header="__('Work Time Model')">
        <livewire:employee.employee-work-time-model-assignment :employeeId="$this->employee->id" />
    </x-card>


</div>
