@props([
    'address'
    ])
<address class="text-xs not-italic">
    <div class="font-semibold">
        {{ $address->company ?? '' }}
    </div>
    <div>
        {{ trim(($address->firstname ?? '') . ' ' . ($address->lastname ?? '')) }}
    </div>
    <div>
        {{ $address->addition ?? '' }}
    </div>
    <div>
        {{ $address->street ?? '' }}
    </div>
    <div>
        {{ trim(($address->zip ?? '') . ' ' . ($address->city ?? '')) }}
    </div>
</address>
