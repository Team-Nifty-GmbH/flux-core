<div>
    <x-modal id="edit-contact-bank-connection">
        <div class="flex flex-col gap-1.5">
            <x-input
                wire:model="contactBankConnection.bank_name"
                :label="__('Name')"
            />
            <x-input
                wire:model="contactBankConnection.account_holder"
                :label="__('Account holder')"
            />
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                x-on:click="$modalClose('edit-contact-bank-connection')"
                :text="__('Cancel')"
            />
            <x-button
                color="primary"
                wire:click="save().then((success) => { if(success) $modalClose('edit-contact-bank-connection'); })"
                primary
                :text="__('Save')"
            />
        </x-slot>
    </x-modal>
    <x-modal id="transaction-details-modal">
        <div class="flex flex-col gap-2">
            <div class="pointer-events-none">
                <x-select.styled
                    :label="__('Bank Connection')"
                    wire:model="transactionForm.contact_bank_connection_id"
                    required
                    disabled
                    select="label:label|value:id|description:description"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\ContactBankConnection::class),
                        'method' => 'POST',
                        'params' => [
                            'where' => [
                                [
                                    'contact_id',
                                    '=',
                                    $contactId,
                                ],
                                [
                                    'is_credit_account',
                                    '=',
                                    true,
                                ],
                            ],
                        ],
                    ]"
                />
            </div>
            <x-number
                step="0.01"
                wire:model="transactionForm.amount"
                :label="__('Transaction Amount')"
            />
        </div>
        <x-slot:footer>
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
        </x-slot>
    </x-modal>
</div>
