@props([
    'bankConnection',
])

<div>
    <div class="font-semibold">
        {{ data_get($bankConnection, 'bank_name', '') }}
    </div>
    <div>
        {{ data_get($bankConnection, 'iban', '') }}
    </div>
    <div>
        {{ data_get($bankConnection, 'bic', '') }}
    </div>
</div>
