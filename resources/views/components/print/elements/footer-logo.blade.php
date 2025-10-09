@props([
    'client',
])

<img
    draggable="false"
    class="max-h-full w-full"
    src="{{ data_get($client, 'logo_small_url', '') }}"
    alt="logo-small"
/>
