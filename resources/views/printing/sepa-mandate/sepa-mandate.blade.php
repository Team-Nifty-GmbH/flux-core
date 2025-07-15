<x-flux::print.first-page-header :address="$model->contact->mainAddress" />
<main class="pt-6">
    <div>
        {{ __('Creditor Identifier') }}
        <span class="font-semibold">
            {{ $model->client->creditor_identifier }}
        </span>
    </div>
    <div>
        {{ __('Mandate Reference Number') }}
        <span class="font-semibold">
            {{ $model->mandate_reference_number }}
        </span>
    </div>
    <div class="py-4">
        {!! $model->type === \FluxErp\Enums\SepaMandateTypeEnum::B2C ? $model->client->sepa_text_b2c : $model->client->sepa_text_b2b !!}
    </div>
    <div class="pt-4">
        <span class="font-semibold">{{ __('Account Holder') }}:</span>
        <span>{{ $model->contactBankConnection?->account_holder }}</span>
    </div>
    <div class="pt-4">
        <span class="font-semibold">{{ __('Street') }}:</span>
        <span>{{ $model->contact->mainAddress->street }}</span>
    </div>
    <div class="pt-4">
        <span class="font-semibold">{{ __('Zip / City') }}:</span>
        <span>
            {{ $model->contact->mainAddress->zip . ' ' . $model->contact->mainAddress->city }}
        </span>
    </div>
    <div class="pt-4">
        <span class="font-semibold">{{ __('Bank Name') }}:</span>
        <span>{{ $model->contactBankConnection?->bank_name }}</span>
    </div>
    <div class="pt-4">
        <span class="font-semibold">{{ __('BIC') }}:</span>
        <span>{{ $model->contactBankConnection?->bic }}</span>
    </div>
    <div class="pt-4">
        <span class="font-semibold">{{ __('IBAN') }}:</span>
        <span>{{ $model->contactBankConnection?->iban }}</span>
    </div>
    <div class="pt-4 font-semibold">
        {{ __('Date, Location and Signature') }}
    </div>
    <div class="mt-8 max-w-sm">
        <hr class="border-black" />
    </div>
</main>
