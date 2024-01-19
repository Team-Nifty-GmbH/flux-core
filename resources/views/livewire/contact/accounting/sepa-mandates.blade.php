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
                <x-features.media.upload-form-object :label="__('Signed Sepa Mandate')" wire:model="signedMandate" :multiple="false" accept="application/pdf, image/jpeg, image/png, image/svg+xml"/>
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
    <x-modal name="create-documents">
        <x-card :title="__('Create Documents')">
            <div class="grid grid-cols-3 gap-1.5">
                <div class="font-semibold text-sm">{{ __('Print') }}</div>
                <div class="font-semibold text-sm">{{ __('Email') }}</div>
                <div class="font-semibold text-sm">{{ __('Download') }}</div>
                @foreach($printLayouts as $printLayout)
                    <x-checkbox wire:model.boolean="selectedPrintLayouts.print.{{ $printLayout }}" :label="__($printLayout)" />
                    <x-checkbox wire:model.boolean="selectedPrintLayouts.email.{{ $printLayout }}" :label="__($printLayout)" />
                    <x-checkbox wire:model.boolean="selectedPrintLayouts.download.{{ $printLayout }}" :label="__($printLayout)" />
                @endforeach
            </div>
            <x-slot:footer>
                <div class="flex justify-end gap-x-4">
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close" />
                        <x-button primary :label="__('Continue')" spinner wire:click="createDocuments().then(() => { close(); });" />
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    @include('tall-datatables::livewire.data-table')
</div>
