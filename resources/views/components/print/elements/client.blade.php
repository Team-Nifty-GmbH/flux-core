@props([
    'client' => null,
])

<div>
    <div class="font-semibold">
        {{ data_get($client, 'name', '') }}
    </div>
    <div>
        {{ data_get($client, 'ceo', '') }}
    </div>
    <div>
        {{ data_get($client, 'street', '') }}
    </div>
    <div>
        {{ trim(data_get($client, 'postcode', '') . ' ' . data_get($client, 'city', '')) }}
    </div>
    <div>
        {{ data_get($client, 'phone', '') }}
    </div>
    <div>
        <div>
            {{ data_get($client, 'vat_id', '') }}
        </div>
    </div>
</div>
