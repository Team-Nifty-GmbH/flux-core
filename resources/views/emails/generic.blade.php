<x-mail::layout>
    {{-- Header --}}
    @php
        $logo = $tenant->getFirstMedia('logo_small') ?? $tenant->getFirstMedia('logo');
        // Outlook ignores max-width/max-height, it needs width and height attributes
        $logoSize = $logo && file_exists($logo->getPath()) ? getimagesize($logo->getPath()) : false;
        $logoScale = $logoSize ? min(1, 100 / max($logoSize[0], $logoSize[1], 1)) : null;
    @endphp
    <x-slot:header>
        <x-mail::header :url="$tenant->website">
            <img
                @if ($logoScale)
                    width="{{ round($logoSize[0] * $logoScale) }}"
                    height="{{ round($logoSize[1] * $logoScale) }}"
                @endif
                style="max-height: 100px; max-width: 100px; width: auto; height: auto; @if($logo?->mime_type === 'image/svg+xml') min-height: 60px @endif"
                alt="{{ $tenant->name }}"
                src="{{ $logo?->getUrl() ?? '#' }}"
            />
        </x-mail::header>
    </x-slot>
    {{-- Body --}}
    {!! data_get($mailMessageForm, 'html_body') !!}

    {{-- Subcopy --}}
    @isset($subcopy)
        <x-slot:subcopy>
            <x-mail::subcopy>
                {{ $subcopy }}
            </x-mail::subcopy>
        </x-slot>
    @endisset

    {{-- Footer --}}
    <x-slot:footer>
        <x-mail::footer>
            © {{ date('Y') }} {{ $tenant->name }}.
            @lang('All rights reserved.')
        </x-mail::footer>
    </x-slot>
</x-mail::layout>
