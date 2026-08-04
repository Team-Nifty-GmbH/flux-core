<x-modal id="edit-loan-modal" :title="__('Loan')" size="xl">
    <div class="flex flex-col gap-1.5">
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
        <x-select.styled
            :label="__('Order')"
            wire:model.number="loan.order_id"
            select="label:label|value:id"
            :request="[
                'url' => route('search', \FluxErp\Models\Order::class),
                'method' => 'POST',
            ]"
        />
        <x-input wire:model="loan.number" :label="__('Number')" />
        <div class="grid grid-cols-1 gap-1.5 md:grid-cols-2">
            <x-number
                wire:model="loan.amount"
                :label="__('Amount')"
                step="0.01"
                placeholder="0.00"
            />
            <x-number
                wire:model="loan.interest_rate"
                :label="__('Interest Rate')"
                suffix="%"
                min="0"
                step="0.001"
                placeholder="0.000"
            />
        </div>
        <div class="grid grid-cols-1 gap-1.5 md:grid-cols-3">
            <x-select.styled
                wire:model="loan.repayment_type_enum"
                :label="__('Repayment Type')"
                required
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
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('edit-loan-modal')"
        />
        <x-button
            color="indigo"
            loading="save"
            :text="__('Save')"
            wire:click="save()"
        />
    </x-slot:footer>
</x-modal>
