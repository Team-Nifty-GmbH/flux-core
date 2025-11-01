<x-modal
    :id="$absenceRequestForm->modalName()"
    persistent
    size="3xl"
    :title="__('Absence Request')"
>
    <div class="flex flex-col gap-4" x-data="{ showSickNote: false }">
        <div class="grid grid-cols-2 gap-4">
            @if ($this->canChooseEmployee())
                <x-select.styled
                    wire:model="absenceRequestForm.employee_id"
                    :label="__('Employee')"
                    required
                    select="label:label|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\Employee::class),
                        'method' => 'POST',
                    ]"
                />
            @endif
        </div>
        <div class="grid grid-cols-2 gap-4">
            <x-select.styled
                wire:model="absenceRequestForm.absence_type_id"
                :label="__('Absence Type')"
                required
                x-on:select="showSickNote = $event.detail.select.affects_sick_leave; if (!showSickNote) $wire.absenceRequestForm.sick_note_issued_date = null"
                select="value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\AbsenceType::class),
                    'method' => 'POST',
                    'params' => [
                        'fields' => [
                            'id',
                            'name',
                            'code',
                            'color',
                            'affects_sick_leave',
                        ],
                        'whereIn' => ! $this->canChooseEmployee() && ! \FluxErp\Actions\AbsenceRequest\ApproveAbsenceRequest::canPerformAction(false)
                            ? [
                                [
                                    'employee_can_create',
                                    [
                                        \FluxErp\Enums\EmployeeCanCreateEnum::Yes->value,
                                        \FluxErp\Enums\EmployeeCanCreateEnum::Approval_required->value,
                                    ]
                                ]
                            ]
                            : [],
                    ],
                ]"
            />
            <x-select.styled
                wire:model="absenceRequestForm.day_part"
                :label="__('Day Part')"
                x-on:select="if ($event.detail.select.value !== '{{ \FluxErp\Enums\AbsenceRequestDayPartEnum::Time->value }}') { $wire.absenceRequestForm.start_time = null; $wire.absenceRequestForm.end_time = null; }"
                required
                select="label:label|value:value"
                :options="\FluxErp\Enums\AbsenceRequestDayPartEnum::valuesLocalized()"
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

            <div
                x-cloak
                x-show="
                    $wire.absenceRequestForm.day_part ===
                        '{{ \FluxErp\Enums\AbsenceRequestDayPartEnum::Time->value }}'
                "
            >
                <x-time
                    wire:model="absenceRequestForm.start_time"
                    :label="__('Start Time')"
                    :step-minute="15"
                />
            </div>

            <div
                x-cloak
                x-show="
                    $wire.absenceRequestForm.day_part ===
                        '{{ \FluxErp\Enums\AbsenceRequestDayPartEnum::Time->value }}'
                "
            >
                <x-time
                    wire:model="absenceRequestForm.end_time"
                    :label="__('End Time')"
                    :step-minute="15"
                />
            </div>
        </div>

        <div x-cloak x-show="showSickNote">
            <x-date
                wire:model="absenceRequestForm.sick_note_issued_date"
                :label="__('Sick Note Issued Date')"
                :hint="__('Date when the sick note was issued by the doctor')"
            />
        </div>

        <x-textarea
            wire:model="absenceRequestForm.reason"
            :label="__('Reason')"
            rows="3"
        />

        <x-select.styled
            wire:model="absenceRequestForm.substitutes"
            :label="__('Substitute')"
            multiple
            select="label:label|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\Employee::class),
                'method' => 'POST',
            ]"
        />

        <x-textarea
            wire:model="absenceRequestForm.substitute_note"
            :label="__('Substitute Note')"
            rows="2"
        />

        <x-toggle
            wire:model="absenceRequestForm.is_emergency"
            :label="__('Is Emergency')"
        />

        @if ($this->canChooseEmployee())
            <x-select.styled
                wire:model="absenceRequestForm.state"
                :label="__('Status')"
                :options="\FluxErp\Enums\AbsenceRequestStateEnum::valuesLocalized()"
            />
        @endif
    </div>

    <x-slot:footer>
        <x-button
            :text="__('Cancel')"
            color="secondary"
            flat
            x-on:click="$modalClose('{{ $absenceRequestForm->modalName() }}')"
        />
        <x-button
            :text="__('Save')"
            color="primary"
            wire:click="save().then((success) => { if(success) $modalClose('{{ $absenceRequestForm->modalName() }}')})"
        />
    </x-slot>
</x-modal>
