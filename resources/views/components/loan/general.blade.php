<x-card class="w-full">
    <div
        class="flex flex-col gap-1.5"
        x-bind:class="!isEditing && 'pointer-events-none'"
    >
        <x-input wire:model="loan.name" :label="__('Name')" required />
        <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
            <x-select.styled
                :label="__('Contact')"
                wire:model.number="loan.contact_id"
                select="label:label|value:id"
                required
                :request="[
                    'url' => route('search', \FluxErp\Models\Contact::class),
                    'method' => 'POST',
                ]"
            />
            <x-select.styled
                :label="__('Ledger Account')"
                wire:model.number="loan.ledger_account_id"
                select="label:name|value:id|description:number"
                required
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                    'method' => 'POST',
                ]"
            />
        </div>
        <div
            x-bind:class="
                $wire.loan.order_id && 'pointer-events-none opacity-60'
            "
        >
            <x-select.styled
                :label="__('Order')"
                wire:model.number="loan.order_id"
                x-bind:readonly="$wire.loan.order_id"
                select="label:label|value:id"
                :request="[
                    'url' => route('search', \FluxErp\Models\Order::class),
                    'method' => 'POST',
                ]"
            />
        </div>
        <x-input wire:model="loan.number" :label="__('Number')" />
        <div class="grid grid-cols-1 gap-1.5 md:grid-cols-3">
            <x-toggle
                wire:model="loan.allows_extra_repayments"
                :label="__('Allows Extra Repayments')"
            />
            <x-number
                wire:model="loan.extra_repayment_allowance_percentage"
                :label="__('Extra Repayment Allowance Percentage')"
                step="0.001"
                min="0"
                max="1"
                :hint="__('Share of the original amount per calendar year')"
            />
            <x-number
                wire:model="loan.extra_repayment_allowance_amount"
                :label="__('Extra Repayment Allowance Amount')"
                step="0.01"
                min="0"
                :hint="__('Leave empty for an uncapped allowance')"
            />
        </div>
    </div>

    <x-slot:footer>
        <div class="pointer-events-none flex flex-col gap-1.5 opacity-60">
            <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
                <x-number
                    wire:model="loan.amount"
                    :label="__('Amount')"
                    step="0.01"
                />
                <x-number
                    wire:model="loan.interest_rate"
                    :label="__('Interest Rate')"
                    suffix="%"
                    step="0.001"
                />
            </div>
            <div class="grid grid-cols-1 gap-1.5 md:grid-cols-3">
                <x-select.styled
                    wire:model="loan.repayment_type_enum"
                    :label="__('Repayment Type')"
                    select="label:label|value:value"
                    :options="\FluxErp\Enums\RepaymentTypeEnum::valuesLocalized()"
                />
                <x-number
                    wire:model="loan.number_of_installments"
                    :label="__('Number Of Installments')"
                    step="1"
                />
                <x-date wire:model="loan.starts_at" :label="__('Starts At')" />
            </div>
            <div class="grid grid-cols-1 gap-1.5 md:grid-cols-3">
                <x-select.styled
                    wire:model="loan.installment_interval_enum"
                    :label="__('Installment Interval')"
                    select="label:label|value:value"
                    :options="\FluxErp\Enums\InstallmentIntervalEnum::valuesLocalized()"
                />
                <x-number
                    wire:model="loan.grace_period_installments"
                    :label="__('Grace Period Installments')"
                    min="0"
                    step="1"
                />
                <x-number
                    wire:model="loan.installment_amount"
                    :label="__('Installment Amount')"
                    step="0.01"
                />
            </div>
        </div>
    </x-slot:footer>
</x-card>
