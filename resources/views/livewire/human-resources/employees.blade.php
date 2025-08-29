<div>
    <x-modal :id="$employeeForm->modalName()" size="xl">
        <x-slot:title>
            {{ $employeeForm->id ? __('Edit Employee') : __('Create Employee') }}
        </x-slot:title>
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
            {{-- Basic Information --}}
            <x-input
                :label="__('Firstname')"
                wire:model="employeeForm.firstname"
            />
            <x-input
                :label="__('Lastname')"
                wire:model="employeeForm.lastname"
            />
            <x-input
                :label="__('Email')"
                type="email"
                wire:model="employeeForm.email"
            />
            <x-input
                :label="__('Employee Number')"
                wire:model="employeeForm.employee_number"
            />
            <x-select.styled
                :label="__('User')"
                wire:model="employeeForm.user_id"
                select="label:name|value:id"
                unfiltered
                :request="['url' => route('search', \FluxErp\Models\User::class), 'method' => 'POST']"
            />
            <x-date
                :label="__('Hire Date')"
                wire:model="employeeForm.hire_date"
            />
            <x-input
                :label="__('Job Title')"
                wire:model="employeeForm.job_title"
            />
            <x-select.styled
                :label="__('Department')"
                wire:model="employeeForm.employee_department_id"
                :options="resolve_static(\FluxErp\Models\EmployeeDepartment::class, 'query')->get()->map(fn($d) => ['label' => $d->name, 'value' => $d->getKey()])->toArray()"
            />
            <div class="sm:col-span-2">
                <x-toggle
                    :label="__('Active')"
                    wire:model="employeeForm.is_active"
                />
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