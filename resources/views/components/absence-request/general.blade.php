<div>
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <x-select.styled
                    disabled
                    wire:model="absenceRequestForm.absence_type_id"
                    :label="__('Absence Type')"
                    select="value:id"
                    :request="[
                        'url' => route('search', \FluxErp\Models\AbsenceType::class),
                        'method' => 'POST',
                    ]"
                    unfiltered
                />
            </div>
            <x-date
                :label="__('Start Date')"
                wire:model="absenceRequestForm.start_date"
                class="mt-1"
            />

            <x-date
                :label="__('End Date')"
                wire:model="absenceRequestForm.end_date"
                class="mt-1"
            />

            <div class="col-span-2">
                <x-select.styled
                    wire:model="absenceRequestForm.substitute_employee_id"
                    :label="__('Substitute')"
                    select="label:label|value:id"
                    :request="[
                        'url' => route('search', \FluxErp\Models\Employee::class),
                        'method' => 'POST',
                    ]"
                    unfiltered
                />
            </div>

            <div class="col-span-2">
                <x-textarea
                    :label="__('Reason for Absence')"
                    wire:model="absenceRequestForm.reason"
                    class="mt-1"
                    rows="4"
                />
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <x-button
                wire:click="$parent.save"
                color="primary"
                :text="__('Save')"
            />
        </div>
    </x-card>
</div>
