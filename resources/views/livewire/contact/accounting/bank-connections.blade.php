<div>
    <x-modal id="edit-contact-bank-connection">
        <div class="flex flex-col gap-1.5">
            <x-input
                wire:model="contactBankConnection.iban"
                :label="__('IBAN')"
            />
            <x-input
                wire:model="contactBankConnection.bic"
                :label="__('BIC')"
            />
            <x-input
                wire:model="contactBankConnection.bank_name"
                :label="__('Bank name')"
            />
            <x-input
                wire:model="contactBankConnection.account_holder"
                :label="__('Account holder')"
            />
        </div>
        <x-slot:footer>
            <div class="flex gap-1.5 justify-end">
                <x-button color="secondary" light
                    x-on:click="$modalClose('edit-contact-bank-connection')"
                    :text="__('Cancel')"
                />
                <x-button color="secondary" light
                    wire:click="save().then((success) => { if(success) $modalClose('edit-contact-bank-connection'); })"
                    primary
                    :text="__('Save')"
                />
            </div>
        </x-slot:footer>
    </x-modal>
    @include('tall-datatables::livewire.data-table')
</div>
