<x-modal id="edit-ledger-booking-modal" :title="__('Ledger Booking')">
    <div class="flex flex-col gap-1.5">
        <x-select.styled
            :label="__('Debit Account')"
            wire:model.number="ledgerBooking.debit_ledger_account_id"
            select="label:name|value:id|description:number"
            required
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                'method' => 'POST',
            ]"
        />
        <x-select.styled
            :label="__('Credit Account')"
            wire:model.number="ledgerBooking.credit_ledger_account_id"
            select="label:name|value:id|description:number"
            required
            unfiltered
            :request="[
                'url' => route('search', \FluxErp\Models\LedgerAccount::class),
                'method' => 'POST',
            ]"
        />
        <x-number
            wire:model="ledgerBooking.amount"
            :label="__('Amount')"
            step="0.01"
            placeholder="0.00"
        />
        <x-date
            wire:model="ledgerBooking.booking_date"
            :label="__('Booking Date')"
        />
        <x-input
            wire:model="ledgerBooking.booking_text"
            :label="__('Booking Text')"
        />
    </div>
    <x-slot:footer>
        <x-button
            color="secondary"
            light
            flat
            :text="__('Cancel')"
            x-on:click="$tsui.close.modal('edit-ledger-booking-modal')"
        />
        <x-button
            color="indigo"
            :text="__('Save')"
            x-on:click="
                $wire.save().then((success) => {
                    if (success) $tsui.close.modal('edit-ledger-booking-modal');
                })
            "
        />
    </x-slot:footer>
</x-modal>
