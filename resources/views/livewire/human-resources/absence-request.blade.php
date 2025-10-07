<div>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5"
    >
        <div class="flex items-center space-x-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    {{ __('Absence Request #:id', ['id' => $absenceRequestForm->id]) }}
                </h1>
                <div class="mt-2 flex items-center gap-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <x-link
                            :href="$this->getEmployeeUrl()"
                            :text="__('Employee') . ': ' . data_get($absenceRequestForm, 'employee.name')"
                        />
                    </div>
                    <div>
                        {{ \FluxErp\Enums\AbsenceRequestStateEnum::from($absenceRequestForm->state_enum)->badge() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-flux::tabs wire:model.live="tab" :$tabs />
</div>
