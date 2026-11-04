<div class="flex flex-col gap-4 pb-4">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-card :header="__('Salary')">
            <div class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <x-number
                    :label="__('Base Salary')"
                    wire:model="employeeSalaryForm.base_salary"
                    :hint="__('Used for the salary types monthly and annual.')"
                    step="0.01"
                    min="0"
                />
                <x-number
                    :label="__('Hourly Rate')"
                    wire:model="employeeSalaryForm.hourly_rate"
                    :hint="__('Used for the salary type hourly, with the weekly hours of the work time model.')"
                    step="0.01"
                    min="0"
                />
                <x-number
                    :label="__('Employer Contribution Rate')"
                    wire:model="employeeSalaryForm.employer_contribution_rate"
                    :hint="__('Employer share of social security in percent of the gross salary.')"
                    step="0.01"
                    min="0"
                    max="100"
                />
            </div>
            <x-slot:footer>
                <div class="flex justify-end">
                    <x-button
                        :text="__('Save')"
                        color="primary"
                        wire:click="saveSalary()"
                    />
                </div>
            </x-slot:footer>
        </x-card>
        <x-card :header="__('Monthly Personnel Costs')">
            <dl class="grid grid-cols-2 gap-y-3 text-sm">
                <dt class="text-gray-500 dark:text-gray-400">
                    {{ __('Gross Salary') }}
                </dt>
                <dd class="text-right" x-text="$wire.costs.gross"></dd>
                <dt class="text-gray-500 dark:text-gray-400">
                    {{ __('Employer Contributions') }}
                </dt>
                <dd
                    class="text-right"
                    x-text="$wire.costs.employer_contributions"
                ></dd>
                <dt class="border-t pt-3 font-semibold dark:border-gray-700">
                    {{ __('Total') }}
                </dt>
                <dd
                    class="border-t pt-3 text-right font-semibold dark:border-gray-700"
                    x-text="$wire.costs.total"
                ></dd>
            </dl>
        </x-card>
    </div>
    <x-modal
        :id="$salaryComponentForm->modalName()"
        :title="__('Salary Component')"
    >
        <div class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input
                    :label="__('Name')"
                    wire:model="salaryComponentForm.name"
                />
            </div>
            <x-number
                :label="__('Amount')"
                wire:model="salaryComponentForm.amount"
                step="0.01"
            />
            <x-select.styled
                :label="__('Payment Interval')"
                wire:model="salaryComponentForm.payment_interval"
                :options="\FluxErp\Enums\PaymentIntervalEnum::valuesLocalized()"
            />
            <x-date
                :label="__('Valid From')"
                wire:model="salaryComponentForm.valid_from"
            />
            <x-date
                :label="__('Valid Until')"
                wire:model="salaryComponentForm.valid_until"
            />
        </div>
        <x-slot:footer>
            <x-button
                :text="__('Cancel')"
                color="secondary"
                flat
                x-on:click="$tsui.close.modal('{{ $salaryComponentForm->modalName() }}')"
            />
            <x-button
                :text="__('Save')"
                color="primary"
                x-on:click="$wire.save().then((success) => {
                    if (success) {
                        $tsui.close.modal('{{ $salaryComponentForm->modalName() }}');
                    }
                })"
            />
        </x-slot:footer>
    </x-modal>
</div>
