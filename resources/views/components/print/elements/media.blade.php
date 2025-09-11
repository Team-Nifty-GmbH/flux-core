@props(['media'])

<div
    class="absolute"
    style="
                height: {{ $media['height'] ?? '1.7' }}cm ;
                width:{{ is_null($media['width']) ? 'auto' : $media['width'] . 'cm'  }};
                left: {{ $media['x'] ?? 0 }}cm;
                top: {{ $media['y'] ?? 0 }}cm;">
    <img
        class="logo-small footer-logo max-h-full w-full"
        src="{{ $media['src'] }}"
    />
</div>
