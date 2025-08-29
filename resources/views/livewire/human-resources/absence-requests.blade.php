<x-modal :id="$absenceRequestForm->modalName()" persistent size="3xl">
    <x-slot:title>{{ __('Absence Request') }}</x-slot:title>

    <div class="flex flex-col gap-4">
        <div class="grid grid-cols-2 gap-4">
            @if($this->canChooseEmployee())
                <x-select.styled
                    wire:model="absenceRequestForm.employee_id"
                    :label="__('Employee')"
                    select="label:label|value:id"
                    :request="[
                        'url' => route('search', \FluxErp\Models\Employee::class),
                        'method' => 'POST',
                    ]"
                    unfiltered
                    required
                />
            @endif

            <x-select.styled
                wire:model="absenceRequestForm.absence_type_id"
                :label="__('Absence Type')"
                select="value:id"
                :request="[
                    'url' => route('search', \FluxErp\Models\AbsenceType::class),
                    'method' => 'POST',
                    'params' => [
                        'whereIn' => ! $this->canChooseEmployee() && ! \FluxErp\Actions\AbsenceRequest\ApproveAbsenceRequest::canPerformAction(false)
                            ? [
                                [
                                    'employee_can_create',
                                    [
                                        \FluxErp\Enums\AbsenceRequestCreationTypeEnum::Yes->value,
                                        \FluxErp\Enums\AbsenceRequestCreationTypeEnum::Approval_required->value,
                                    ]
                                ]
                            ]
                            : [],
                    ],
                ]"
                unfiltered
                required
            />
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-date
                wire:model="absenceRequestForm.start_date"
                :label="__('Start Date')"
                required
            />

            <x-date
                wire:model="absenceRequestForm.end_date"
                :label="__('End Date')"
                required
            />
        </div>

        <x-date
            wire:model="absenceRequestForm.sick_note_issued_date"
            :label="__('Sick Note Issued Date')"
            :hint="__('Date when the sick note was issued by the doctor')"
        />

        <x-textarea
            wire:model="absenceRequestForm.reason"
            :label="__('Reason')"
            rows="3"
        />

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

        <x-textarea
            wire:model="absenceRequestForm.substitute_note"
            :label="__('Note for Substitute')"
            rows="2"
        />

        <x-toggle
            wire:model="absenceRequestForm.is_emergency"
            :label="__('Emergency Request')"
        />

        @if($this->canChooseEmployee())
            <x-select.styled
                wire:model="absenceRequestForm.status"
                :label="__('Status')"
                :options="\FluxErp\Enums\AbsenceRequestStatusEnum::valuesLocalized()"
            />
        @endif
    </div>

    <x-slot:footer>
        <x-button :text="__('Cancel')" color="secondary" flat x-on:click="$modalClose('{{ $absenceRequestForm->modalName() }}')" />
        <x-button :text="__('Save')" color="primary" wire:click="save().then((success) => { if(success) $modalClose('{{ $absenceRequestForm->modalName() }}')})" />
    </x-slot:footer>
</x-modal>
