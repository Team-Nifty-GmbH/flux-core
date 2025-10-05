<div>
    <x-modal :id="$employeeForm->modalName()" xl :title="__('Create Employee')">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-select.styled
                    :label="__('User')"
                    wire:model="employeeForm.user_id"
                    x-on:select="$wire.employeeForm.firstname = $event.detail.select?.firstname; $wire.employeeForm.lastname = $event.detail.select?.lastname; $wire.employeeForm.email = $event.detail.select?.email"
                    select="value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\User::class),
                        'method' => 'POST',
                        'params' => [
                            'whereDoesntHave' => 'employee',
                            'fields' => [
                                'id',
                                'firstname',
                                'lastname',
                                'email',
                            ],
                            'with' => 'media',
                        ],
                    ]"
                />
            </div>
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
            <x-date
                :label="__('Employment Date')"
                wire:model="employeeForm.employment_date"
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
             <x-select.styled
                :label="__('Work Time Model')"
                wire:model="employeeForm.work_time_model_id"
                required
                select="label:name|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\WorkTimeModel::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name'],
                        'where' => [
                            [
                                'is_active',
                                '=',
                                true
                            ]
                        ]
                    ]
                ]"
            />
        </div>
        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                x-on:click="$modalClose('{{ $employeeForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="save().then((success) => {
                    if (success) {
                        $modalClose('{{ $employeeForm->modalName() }}');
                    }
                })"
            />
        </x-slot:footer>
    </x-modal>
</div>
