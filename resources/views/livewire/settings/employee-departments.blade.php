<div>
    <x-modal
        :id="$employeeDepartmentForm->modalName()"
        size="2xl"
        :title="__('Department')"
    >
        <div class="flex flex-col gap-4">
            <x-input
                wire:model="employeeDepartmentForm.name"
                :label="__('Name')"
                required
            />

            <x-input
                wire:model="employeeDepartmentForm.code"
                :label="__('Code')"
                :hint="__('Unique department code')"
            />

            <x-textarea
                wire:model="employeeDepartmentForm.description"
                :label="__('Description')"
                rows="3"
            />

            <x-select.styled
                wire:model="employeeDepartmentForm.parent_id"
                :label="__('Parent Department')"
                :clearable="true"
                select="label:name|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\EmployeeDepartment::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name', 'code'],
                        'where' => [
                            ['id', '!=', $employeeDepartmentForm->id]
                        ]
                    ]
                ]"
            />

            <x-select.styled
                wire:model="employeeDepartmentForm.location_id"
                :label="__('Location')"
                :clearable="true"
                select="label:name|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Location::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name']
                    ]
                ]"
            />

            <x-select.styled
                wire:model="employeeDepartmentForm.manager_employee_id"
                :label="__('Department Manager')"
                select="label:name|value:id"
                :clearable="true"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Employee::class),
                    'method' => 'POST',
                    'params' => [
                        'searchFields' => ['name', 'email']
                    ]
                ]"
            />

            <x-toggle
                wire:model="employeeDepartmentForm.is_active"
                :label="__('Is Active')"
            />
        </div>

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('{{ $employeeDepartmentForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="save().then((success) => {
                    if (success) {
                        $modalClose('{{ $employeeDepartmentForm->modalName() }}');
                    }
                })"
            />
        </x-slot>
    </x-modal>
</div>
