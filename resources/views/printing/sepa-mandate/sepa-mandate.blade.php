<x-flux::print.first-page-header :address="$model->contact->mainAddress" />
<main style="padding-top: 24px">
    <div>
        {{ __('Creditor Identifier') }}
        <span style="font-weight: 600">
            {{ $model->tenant->creditor_identifier }}
        </span>
    </div>
    <div>
        {{ __('Mandate Reference Number') }}
        <span style="font-weight: 600">
            {{ $model->mandate_reference_number }}
        </span>
    </div>
    <div style="padding-top: 16px; padding-bottom: 16px">
        {!! $model->sepa_mandate_type_enum === \FluxErp\Enums\SepaMandateTypeEnum::BASIC ? $tenant->sepa_text_basic : $tenant->sepa_text_b2b !!}
    </div>
    <div style="padding-top: 16px">
        <span style="font-weight: 600">{{ __('Account Holder') }}:</span>
        <span>{{ $model->contactBankConnection?->account_holder }}</span>
    </div>
    <div style="padding-top: 16px">
        <span style="font-weight: 600">{{ __('Street') }}:</span>
        <span>{{ $model->contact->mainAddress->street }}</span>
    </div>
    <div style="padding-top: 16px">
        <span style="font-weight: 600">{{ __('Zip / City') }}:</span>
        <span>
            {{ $model->contact->mainAddress->zip . ' ' . $model->contact->mainAddress->city }}
        </span>
    </div>
    <div style="padding-top: 16px">
        <span style="font-weight: 600">{{ __('Bank Name') }}:</span>
        <span>{{ $model->contactBankConnection?->bank_name }}</span>
    </div>
    <div style="padding-top: 16px">
        <span style="font-weight: 600">{{ __('BIC') }}:</span>
        <span>{{ $model->contactBankConnection?->bic }}</span>
    </div>
    <div style="padding-top: 16px">
        <span style="font-weight: 600">{{ __('IBAN') }}:</span>
        <span>{{ $model->contactBankConnection?->iban }}</span>
    </div>
    <div style="padding-top: 16px; font-weight: 600">
        {{ __('Date, Location and Signature') }}
    </div>
    <div style="margin-top: 32px; max-width: 384px">
        <hr style="border: 0; border-top: 1px solid black" />
    </div>
</main>
