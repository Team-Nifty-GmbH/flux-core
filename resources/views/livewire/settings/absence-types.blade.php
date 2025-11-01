<div>
    <x-modal :id="$absenceTypeForm->modalName()" :title="__('Absence Type')">
        <div class="flex flex-col gap-4">
            <div
                class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20"
            >
                <h4 class="mb-3 font-medium text-blue-900 dark:text-blue-100">
                    {{ __('How Absence Types Work') }}
                </h4>

                <div class="space-y-3 text-sm text-blue-800 dark:text-blue-200">
                    <div class="space-y-1">
                        <p class="font-semibold">
                            {{ __('Absence Type Categories') . ':' }}
                        </p>
                        <p>
                            <strong>{{ __('Sick Leave') . ':' }}</strong>
                            {{ __('Medical absence, usually with doctor\'s note') }}
                        </p>
                        <p>
                            <strong>{{ __('Vacation') . ':' }}</strong>
                            {{ __('Uses vacation days balance') }}
                        </p>
                        <p>
                            <strong>
                                {{ __('Overtime Compensation') . ':' }}
                            </strong>
                            {{ __('Uses accumulated overtime hours') }}
                        </p>
                        <p>
                            <strong>
                                {{ __('Normal Attendance') . ':' }}
                            </strong>
                            {{ __('Counts as work time (e.g., training, business school)') }}
                        </p>
                    </div>

                    <div
                        class="border-t border-blue-300 pt-3 dark:border-blue-700"
                    >
                        <p class="mb-1 font-semibold">
                            {{ __('Percentage Deduction - How it affects working hours') . ':' }}
                        </p>
                        <p>
                            <strong>
                                {{ __(':percentage% Deduction', ['percentage' => 0]) . ':' }}
                            </strong>
                            {{ __('Full absence → 0h work recorded, -8h overtime') }}
                        </p>
                        <p>
                            <strong>
                                {{ __(':percentage% Deduction', ['percentage' => 50]) . ':' }}
                            </strong>
                            {{ __('Half day absence → 4h work recorded, -4h overtime') }}
                        </p>
                        <p>
                            <strong>
                                {{ __(':percentage% Deduction', ['percentage' => 100]) . ':' }}
                            </strong>
                            {{ __('Full day counts as worked → 8h work recorded, 0h overtime') }}
                        </p>
                    </div>

                    <div
                        class="rounded border border-amber-300 bg-amber-50 p-2 dark:border-amber-700 dark:bg-amber-900/20"
                    >
                        <p class="text-amber-900 dark:text-amber-100">
                            <strong>
                                {{ __('Example - Business School') . ':' }}
                            </strong>
                            <br />
                            {{ __('Set to "Normal Attendance" with 100% deduction = Fulfills 8h target, no overtime generated or used') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-input
                    wire:model="absenceTypeForm.name"
                    :label="__('Name')"
                    required
                />
                <x-input
                    wire:model="absenceTypeForm.code"
                    :label="__('Code')"
                    required
                />
            </div>

            <x-color
                wire:model="absenceTypeForm.color"
                :label="__('Color')"
                required
            />

            <x-select.styled
                multiple
                wire:model="absenceTypeForm.absence_policies"
                :label="__('Absence Policy')"
                select="label:name|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\AbsencePolicy::class),
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

            <x-number
                wire:model="absenceTypeForm.percentage_deduction"
                :label="__('Percentage Deduction')"
                suffix="%"
                min="0"
                max="100"
                step="0.01"
                :hint="__('Percentage of working hours deducted from the day (default: 100%)')"
            />

            <x-select.styled
                wire:model="absenceTypeForm.employee_can_create"
                :label="__('Employee Can Create')"
                required
                select="label:label|value:value"
                :options="\FluxErp\Enums\EmployeeCanCreateEnum::valuesLocalized()"
            />

            <div class="rounded-lg border p-4">
                <h3 class="mb-3 font-medium">{{ __('Absence Type') }}</h3>
                <div
                    class="space-y-3"
                    x-data="{
                        get absenceType() {
                            if ($wire.absenceTypeForm.affects_sick_leave) return 'sick'
                            if ($wire.absenceTypeForm.affects_vacation) return 'vacation'
                            if ($wire.absenceTypeForm.affects_overtime) return 'overtime'
                            return 'none'
                        },
                        set absenceType(value) {
                            $wire.absenceTypeForm.affects_sick_leave = value === 'sick'
                            $wire.absenceTypeForm.affects_vacation = value === 'vacation'
                            $wire.absenceTypeForm.affects_overtime = value === 'overtime'
                        },
                    }"
                >
                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            type="radio"
                            x-model="absenceType"
                            value="none"
                            class="mt-1"
                        />
                        <div>
                            <div class="font-medium">
                                {{ __('Normal Attendance') }}
                            </div>
                            <div
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ __('Counts as normal attendance and fulfills target hours') }}
                            </div>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            type="radio"
                            x-model="absenceType"
                            value="sick"
                            class="mt-1"
                        />
                        <div>
                            <div class="font-medium">
                                {{ __('Sick Leave') }}
                            </div>
                            <div
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ __('Highest priority - overrides vacation and overtime when overlapping') }}
                            </div>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            type="radio"
                            x-model="absenceType"
                            value="vacation"
                            class="mt-1"
                        />
                        <div>
                            <div class="font-medium">{{ __('Vacation') }}</div>
                            <div
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ __('Deducts from vacation days balance - overrides overtime when overlapping') }}
                            </div>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            type="radio"
                            x-model="absenceType"
                            value="overtime"
                            class="mt-1"
                        />
                        <div>
                            <div class="font-medium">
                                {{ __('Overtime Compensation') }}
                            </div>
                            <div
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ __('Deducts from overtime hours balance') }}
                            </div>
                        </div>
                    </label>

                    <div
                        class="mt-3 rounded bg-blue-50 p-2 text-sm text-blue-600 dark:bg-blue-900/20 dark:text-blue-400"
                    >
                        {{ __('Priority for overlapping absences: Sick Leave > Vacation > Overtime > Other') }}
                    </div>
                </div>
            </div>

            <x-toggle
                wire:model="absenceTypeForm.is_active"
                :label="__('Is Active')"
            />
        </div>

        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('{{ $absenceTypeForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="save().then((success) => { if(success) $modalClose('{{ $absenceTypeForm->modalName() }}') })"
            />
        </x-slot>
    </x-modal>
</div>
