@props([
    'media',
])

<div
    class="absolute"
    style="
        height: {{ $media['height'] ?? '1.7' }}cm;
        width: {{ is_null($media['width']) ? 'auto' : $media['width'] . 'cm' }};
        left: {{ $media['x'] ?? 0 }}cm;
        top: {{ $media['y'] ?? 0 }}cm;
    "
>
    <img
        class="max-h-full w-auto max-w-full"
        src="{{ data_get($media, 'src', '') }}"
    />
</div>
