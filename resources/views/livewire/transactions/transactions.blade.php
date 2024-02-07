<x-modal name="transaction-details" max-width="6xl">
    <x-card class="flex flex-col gap-3">
        <x-select
            :label="__('Bank Connection')"
            wire:model="transactionForm.bank_connection_id"
            :options="$bankConnections"
            option-value="id"
            option-label="name"
            option-description="iban"
        />
        <x-datetime-picker without-time wire:model="transactionForm.booking_date" :label="__('Booking Date')"/>
        <x-datetime-picker without-time wire:model="transactionForm.value_date" :label="__('Value Date')"/>
        <x-input wire:model="transactionForm.counterpart_name" :label="__('Counterpart Name')"/>
        <x-input wire:model="transactionForm.counterpart_iban" :label="__('Counterpart IBAN')"/>
        <x-input wire:model="transactionForm.counterpart_bank_name" :label="__('Counterpart Bank Name')"/>
        <x-textarea wire:model="transactionForm.purpose" :label="__('Purpose')"/>
        <x-inputs.number step="0.01" wire:model="transactionForm.amount" :label="__('Amount')"/>
        <x-slot:footer>
            <div class="flex justify-between">
                <x-button
                    :label="__('Delete')"
                    flat
                    negative
                    wire:click="deleteTransaction().then((success) => {if(success) close();})"
                    wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Transaction')]) }}"
                />
                <div class="w-full flex justify-end gap-1.5">
                    <x-button :label="__('Cancel')" x-on:click="close"/>
                    <x-button primary :label="__('Save')" wire:click="saveTransaction().then((success) => {if(success) close();})"/>
                </div>
            </div>
        </x-slot:footer>
    </x-card>
</x-modal>
