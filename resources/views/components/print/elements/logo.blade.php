@props(['client','logo'])

<div
    class="absolute"
    style="
                height: {{ $logo['height'] ?? '1.7' }}cm ;
                width:{{ is_null($logo['width']) ? 'auto' : $logo['width'] . 'cm'  }};
                left: {{ $logo['x'] }}cm;
                top: {{ $logo['y'] }}cm;">
    <img
        class="logo-small footer-logo max-h-full w-full"
        src="{{ $client->logo_small }}"
    />
</div>
