{{ $this->renderCreateDocumentsModal() }}
<x-modal id="edit-sepa-mandate">
    <div class="flex flex-col gap-1.5">
        <x-select.styled
            wire:model="sepaMandate.contact_bank_connection_id"
            :label="__('Bank connection')"
            select="label:iban|value:id"
            option-description="bank_name"
            :options="$contactBankConnections"
        />
        <x-date
            wire:model="sepaMandate.signed_date"
            :label="__('Signed Date')"
            :without-time="true"
        />
        <x-flux::features.media.upload-form-object :label="__('Signed Sepa Mandate')" wire:model="signedMandate" :multiple="false" accept="application/pdf, image/jpeg, image/png, image/svg+xml"/>
    </div>
    <x-slot:footer>
        <div class="flex gap-1.5 justify-end">
            <x-button color="secondary" light
                x-on:click="$modalClose('execute-payment-run')"
                :text="__('Cancel')"
            />
            <x-button color="secondary" light
                wire:click="save().then((success) => { if(success) $modalClose('execute-payment-run'); })"
                primary
                :text="__('Save')"
            />
        </div>
    </x-slot:footer>
</x-modal>
