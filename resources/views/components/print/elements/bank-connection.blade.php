@props(['bankConnection'])

<div>
    <div class="font-semibold">
        {{ $bankConnection->bank_name ?? '' }}
    </div>
    <div>
        {{ $bankConnection->iban ?? '' }}
    </div>
    <div>
        {{ $bankConnection->bic ?? '' }}
    </div>
</div>
