@extends('flux::livewire.transactions.transactions')
<x-modal id="assign-order-modal" size="7xl">
    <x-card>
        <div class="grid grid-cols-2 gap-1.5">
            <div class="flex flex-col gap-1.5">
                <x-select.styled
                    readonly
                    :label="__('Bank Connection')"
                    wire:model="transactionForm.bank_connection_id"
                    select="label:name|value:id|description:iban"
                    :options="$bankConnections"
                />
                <x-date readonly without-time wire:model="transactionForm.booking_date" :label="__('Booking Date')"/>
                <x-date readonly without-time wire:model="transactionForm.value_date" :label="__('Value Date')"/>
                <x-input readonly wire:model="transactionForm.counterpart_name" :label="__('Counterpart Name')"/>
                <x-input readonly wire:model="transactionForm.counterpart_iban" :label="__('Counterpart IBAN')"/>
                <x-input readonly wire:model="transactionForm.counterpart_bank_name" :label="__('Counterpart Bank Name')"/>
                <x-textarea readonly wire:model="transactionForm.purpose" :label="__('Purpose')"/>
                <x-number readonly step="0.01" wire:model="transactionForm.amount" :label="__('Amount')"/>
            </div>
            <div class="overflow-auto">
                <template x-for="(child, index) in $wire.transactionForm.children">
                    <x-flux::list-item :item="[]">
                        <x-slot:value>
                            <span x-text="child.order?.invoice_number + ' (' + window.formatters.date(child.order?.invoice_date) + ')'"></span>
                        </x-slot:value>
                        <x-slot:sub-value>
                            <div class="flex flex-col">
                                <span x-text="child.order?.contact.invoice_address.name"></span>
                                <span x-html="'{{ __('Total') }}' + ': ' + window.formatters.coloredMoney(child.order?.total_gross_price)"></span>
                                <span x-html="'{{ __('Balance') }}' + ': ' + window.formatters.coloredMoney(child.order?.balance)"></span>
                            </div>
                        </x-slot:sub-value>
                        <x-slot:actions>
                            <x-number x-model="child.amount" step="0.01" />
                            <x-button color="red" icon="trash" x-on:click="$wire.transactionForm.children.splice(index, 1); $wire.recalculateDifference();"/>
                        </x-slot:actions>
                    </x-flux::list-item>
                </template>
                <div class="flex justify-end gap-1.5">
                    <div>{{ __('Difference') }}</div>
                    <div x-html="window.formatters.coloredMoney($wire.transactionForm.difference)"></div>
                </div>
            </div>
        </div>
        <div class="pt-4">
            <livewire:accounting.order-list />
        </div>
    </x-card>
    <x-slot:footer>
        <x-button color="secondary" light :text="__('Cancel')" x-on:click="$modalClose('assign-order-modal')"/>
        <x-button color="indigo" :text="__('Save')" wire:click="saveAssignment().then((success) => {if(success) $modalClose('assign-order-modal');})"/>
    </x-slot:footer>
</x-modal>
