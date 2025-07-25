<x-modal id="transaction-details-modal" size="6xl">
    <div class="flex flex-col gap-1.5">
        <x-select.styled
            :label="__('Bank Connection')"
            wire:model="transactionForm.bank_connection_id"
            select="label:name|value:id|description:iban"
            :options="$bankConnections"
        />
        <x-date
            without-time
            wire:model="transactionForm.booking_date"
            :label="__('Booking Date')"
        />
        <x-date
            without-time
            wire:model="transactionForm.value_date"
            :label="__('Value Date')"
        />
        <x-input
            wire:model="transactionForm.counterpart_name"
            :label="__('Counterpart Name')"
        />
        <x-input
            wire:model="transactionForm.counterpart_iban"
            :label="__('Counterpart IBAN')"
        />
        <x-input
            wire:model="transactionForm.counterpart_bank_name"
            :label="__('Counterpart Bank Name')"
        />
        <x-select.styled
            multiple
            wire:model.number="transactionForm.categories"
            :label="__('Categories')"
            select="label:label|value:id"
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\Category::class),
                'method' => 'POST',
                'params' => [
                    'where' => [
                        [
                            'model_type',
                            '=',
                            morph_alias(\FluxErp\Models\Transaction::class),
                        ],
                    ],
                ],
            ]"
        />
        <x-textarea
            wire:model="transactionForm.purpose"
            :label="__('Purpose')"
        />
        <x-number
            step="0.01"
            wire:model="transactionForm.amount"
            :label="__('Amount')"
        />
    </div>
    <x-slot:footer>
        <div class="flex justify-between">
            <x-button
                :text="__('Delete')"
                flat
                color="red"
                wire:click="deleteTransaction().then((success) => {if(success) $modalClose('transaction-detail-modal');})"
                wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Transaction')]) }}"
            />
            <div class="flex w-full justify-end gap-x-2">
                <x-button
                    color="secondary"
                    light
                    :text="__('Cancel')"
                    x-on:click="$modalClose('transaction-details-modal')"
                />
                <x-button
                    color="indigo"
                    :text="__('Save')"
                    wire:click="saveTransaction().then((success) => {if(success) $modalClose('transaction-details-modal');})"
                />
            </div>
        </div>
    </x-slot>
</x-modal>
