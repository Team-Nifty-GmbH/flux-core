@props([
    'client',
])

<img
    draggable="false"
    class="max-h-full w-auto max-w-full"
    src="{{ data_get($client, 'logo_small_url', '') }}"
    alt="logo-small"
/>
