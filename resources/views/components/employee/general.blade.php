<div class="space-y-6 pb-16">
    {{-- Basic Information --}}
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

    {{-- Personal Information --}}
    <x-card :header="__('Personal Information')">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
            <x-date
                :label="__('Date of Birth')"
                wire:model="employee.date_of_birth"
                x-bind:disabled="!edit"
            />
            <x-input
                :label="__('Place of Birth')"
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
                :label="__('ZIP')"
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
                :label="__('Number of Children')"
                wire:model="employee.number_of_children"
                x-bind:disabled="!edit"
            />
        </div>
    </x-card>

    {{-- Contact Information --}}
    <x-card :header="__('Contact Information')">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
            <x-input
                :label="__('Mobile Phone')"
                wire:model="employee.mobile_phone"
                x-bind:disabled="!edit"
            />
        </div>
    </x-card>

    {{-- Government & Insurance --}}
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
                :label="__('Fixed-Term Contract Until')"
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
                :label="__('Department')"
                wire:model="employee.employee_department_id"
                :options="resolve_static(\FluxErp\Models\EmployeeDepartment::class, 'query')->get()->map(fn($d) => ['label' => $d->name, 'value' => $d->getKey()])->toArray()"
                x-bind:disabled="!edit"
            />
            <x-select.styled
                :label="__('Location')"
                wire:model="employee.location_id"
                :options="resolve_static(\FluxErp\Models\Location::class, 'query')->get()->map(fn($l) => ['label' => $l->name, 'value' => $l->getKey()])->toArray()"
                x-bind:disabled="!edit"
            />
        </div>
    </x-card>

    {{-- Work Time Model Assignment --}}
    <x-card :header="__('Work Time Model')">
        <livewire:human-resources.employee.employee-work-time-model-assignment :employee="$this->employee->id ? resolve_static(\FluxErp\Models\Employee::class, 'query')->whereKey($this->employee->id)->first() : null" :key="'work-time-model-' . $this->employee->id" />
    </x-card>


</div>
