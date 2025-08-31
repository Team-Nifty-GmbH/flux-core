<div>
    <x-modal :id="$employeeBalanceAdjustmentForm->modalName()">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
            <x-select.styled
                :label="__('Type')"
                wire:model="employeeBalanceAdjustmentForm.type"
                :options="\FluxErp\Enums\EmployeeBalanceAdjustmentTypeEnum::valuesLocalized()"
            />
            <x-number
                :label="__('Amount')"
                wire:model="employeeBalanceAdjustmentForm.amount"
                :hint="__('Positive = Addition, Negative = Deduction')"
                step="0.01"
            />
            <x-date
                :label="__('Effective Date')"
                wire:model="employeeBalanceAdjustmentForm.effective_date"
            />
            <x-select.styled
                :label="__('Reason')"
                wire:model="employeeBalanceAdjustmentForm.reason"
                :options="\FluxErp\Enums\EmployeeBalanceAdjustmentReasonEnum::valuesLocalized()"
            />
            <div class="sm:col-span-2">
                <x-textarea
                    :label="__('Description')"
                    wire:model="employeeBalanceAdjustmentForm.description"
                    rows="3"
                />
            </div>
        </div>
        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$modalClose('{{ $employeeBalanceAdjustmentForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                wire:click="save().then((success) => {
                    if (success) {
                        $modalClose('{{ $employeeBalanceAdjustmentForm->modalName() }}');
                    }
                })"
            />
        </x-slot:footer>
    </x-modal>
</div>
