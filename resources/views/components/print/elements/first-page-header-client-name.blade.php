@props([
    'client',
])

<div draggable="false" class="text-5xl font-semibold">
    {{ data_get($client, 'name', '') }}
</div>
