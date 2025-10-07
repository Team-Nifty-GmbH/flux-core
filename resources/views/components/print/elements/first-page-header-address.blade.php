@props([
    'address',
])

<address draggable="false" class="mb-0 select-none pb-0 text-xs not-italic">
    <div class="font-semibold">
        {{ data_get($address, 'company', '') }}
    </div>
    <div>
        {{ trim(($address->firstname ?? '') . ' ' . ($address->lastname ?? '')) }}
    </div>
    <div>
        {{ data_get($address, 'addition', '') }}
    </div>
    <div>
        {{ data_get($address, 'street', '') }}
    </div>
    <div>
        {{ trim(data_get($address, 'zip', '') . ' ' . data_get($address, 'city', '')) }}
    </div>
</address>
