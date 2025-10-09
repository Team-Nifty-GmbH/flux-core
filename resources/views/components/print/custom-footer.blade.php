@props([
    'footerLayout',
    'client',
])

@php
    $bankConnections = data_get($client, 'bankConnections');
    $bankIds = array_map(fn ($item) => 'footer-bank-' . data_get($item, 'id', uniqid()), is_null($bankConnections) ? [] : $bankConnections->toArray());
@endphp

<footer
    style="height: {{ data_get($footerLayout, 'height', 0) }}cm"
    class="border-semi-black w-full border-t bg-white text-2xs leading-3"
>
    @foreach (data_get($footerLayout, 'elements', []) as $element)
        {{-- client --}}
        @if (data_get($element, 'id', '') === 'footer-client-' . data_get($client, 'id', ''))
            <address
                style="
                    left: {{ data_get($element, 'x', 0) }}cm;
                    top: {{ data_get($element, 'y', 0) }}cm;
                "
                class="absolute not-italic"
            >
                <x-flux::print.elements.client :client="$client" />
            </address>
        @endif

        {{-- logo --}}
        @if (data_get($element, 'id', '') === 'footer-logo' && data_get($client, 'logo_small', null))
            <div
                class="absolute"
                style="
                    height: {{ data_get($element, 'height', '1.7') }}cm;
                    width: {{ is_null(data_get($element, 'width')) ? 'auto' : data_get($element, 'width') . 'cm' }};
                    left: {{ data_get($element, 'x', '0') }}cm;
                    top: {{ data_get($element, 'y', '0') }}cm;
                "
            >
                <x-flux::print.elements.footer-logo :client="$client" />
            </div>
        @endif

        {{-- bank connection --}}
        @if (in_array(data_get($element, 'id'), $bankIds))
            @php
                $index = array_search(data_get($element, 'id'), $bankIds);
                $bank = is_numeric($index) ? data_get($bankConnections, $index) : null;
            @endphp

            @if ($bank)
                <div
                    style="
                        left: {{ data_get($element, 'x', 0) }}cm;
                        top: {{ data_get($element, 'y', 0) }}cm;
                    "
                    class="absolute"
                >
                    <x-flux::print.elements.bank-connection
                        :bank-connection="$bank"
                    />
                </div>
            @endif
        @endif
    @endforeach

    {{-- media --}}
    @if (data_get($footerLayout, 'media'))
        @foreach (data_get($footerLayout, 'media', []) as $media)
            <x-flux::print.elements.media :media="$media" />
        @endforeach
    @endif

    {{-- snippets --}}
    @if (data_get($footerLayout, 'snippets'))
        @foreach (data_get($footerLayout, 'snippets', []) as $snippet)
            <x-flux::print.elements.snippet :snippet="$snippet" />
        @endforeach
    @endif
</footer>
