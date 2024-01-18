<div>
    <x-modal name="edit-sepa-mandate">
        <x-card>
            <div class="flex flex-col gap-1.5">
                <x-select
                    wire:model="sepaMandate.contact_bank_connection_id"
                    :label="__('Bank connection')"
                    option-value="id"
                    option-label="iban"
                    option-description="bank_name"
                    :options="$contactBankConnections"
                />
                <x-datetime-picker
                    wire:model="sepaMandate.signed_date"
                    :label="__('Signed Date')"
                    :without-time="true"
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
