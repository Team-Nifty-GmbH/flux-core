<div>
    <x-modal name="edit-contact-bank-connection">
        <x-card>
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
                    <x-button
                        x-on:click="close()"
                        :label="__('Cancel')"
                    />
                    <x-button
                        wire:click="save().then((success) => { if(success) close(); })"
                        primary
                        :label="__('Save')"
                    />
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    @include('tall-datatables::livewire.data-table')
</div>
