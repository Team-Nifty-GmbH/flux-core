<div>
    <x-card>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <x-select.styled
                disabled
                wire:model="absenceRequestForm.absence_type_id"
                :label="__('Absence Type')"
                select="value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\AbsenceType::class),
                    'method' => 'POST',
                ]"
            />

            <x-select.styled
                wire:model="absenceRequestForm.day_part"
                :label="__('Day Part')"
                x-on:select="if ($event.detail.select.value !== '{{ \FluxErp\Enums\AbsenceRequestDayPartEnum::Time }}') { $wire.absenceRequestForm.start_time = null; $wire.absenceRequestForm.end_time = null; }"
                required
                select="label:label|value:value"
                :options="\FluxErp\Enums\AbsenceRequestDayPartEnum::valuesLocalized()"
            />

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

            <div
                x-cloak
                x-show="
                    $wire.absenceRequestForm.day_part ===
                        '{{ \FluxErp\Enums\AbsenceRequestDayPartEnum::Time }}'
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
                        '{{ \FluxErp\Enums\AbsenceRequestDayPartEnum::Time }}'
                "
            >
                <x-time
                    wire:model="absenceRequestForm.end_time"
                    :label="__('End Time')"
                    :step-minute="15"
                />
            </div>

            <div x-cloak x-show="$wire.affectsSickLeave">
                <x-date
                    wire:model="absenceRequestForm.sick_note_issued_date"
                    :label="__('Sick Note Issued Date')"
                />
            </div>

            <div class="col-span-2">
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
            </div>

            <div class="col-span-2">
                <x-textarea
                    :label="__('Reason')"
                    wire:model="absenceRequestForm.reason"
                    class="mt-1"
                    rows="4"
                />
            </div>

            <div class="col-span-2">
                <x-textarea
                    wire:model="absenceRequestForm.substitute_note"
                    :label="__('Substitute Note')"
                    rows="2"
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
