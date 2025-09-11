@props([
    'client' => null,
])

<div>
    <div class="font-semibold">
    {{ $client->name ?? '' }}
    </div>
    <div>
        {{ $client->ceo ?? '' }}
    </div>
    <div>
        {{ $client->street ?? '' }}
    </div>
    <div>
        {{ trim(($client->postcode ?? '') . ' ' . ($client->city ?? '')) }}
    </div>
    <div>
        {{ $client->phone ?? '' }}
    </div>
    <div>
        <div>
            {{ $client->vat_id }}
        </div>
    </div>
</div>
