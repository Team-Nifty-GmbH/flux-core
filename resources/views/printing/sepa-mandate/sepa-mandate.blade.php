<x-layouts.print>
    <x-print.first-page-header :address="$model->contact->mainAddress" />
    <main>
        <div class="font-semibold">
            {{ __('Sepa Mandate') }}
        </div>
        <div>
            {{ __('Creditor Identifier') }} {{ $model->client->creditor_identifier }}
        </div>
        <div>
            {{ __('Mandate Reference Number') }} {{ $model->mandate_reference_number }}
        </div>
        <div>
            {!! $model->client->sepa_text !!}
        </div>
        <div class="mt-4">
            {{ __('Account Holder') }}: {{ $model->contactBankConnection->account_holder }}
        </div>
        <div>
            {{ __('Street') }}: {{ $model->contact->mainAddress->street }}
        </div>
        <div>
            {{ __('Zip / City') }}: {{ $model->contact->mainAddress->zip . ' ' . $model->contact->mainAddress->city }}
        </div>
        <div>
            {{ __('Bank Name') }}: {{ $model->contactBankConnection->bank_name }}
        </div>
        <div>
            {{ __('BIC') }}: {{ $model->contactBankConnection->bic }}
        </div>
        <div>
            {{ __('IBAN') }}: {{ $model->contactBankConnection->iban }}
        </div>
        <div>
            {{ __('Date, Location and Signature') }}
        </div>
        <div class="mt-8 max-w-sm">
            <hr class="border-black" />
        </div>
    </main>
</x-layouts.print>
