@props([
    'footerLayout' => null,
])

@php
    $bankConnections = data_get($client, 'bankConnections');
    $bankIds = array_map(fn ($item) => 'footer-bank-' . data_get($item, 'id', uniqid()), is_null($bankConnections) ? [] : $bankConnections->toArray());
@endphp

@if ($footerLayout)
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
@else
    {{-- default footer if no layout saved --}}
    <footer class="h-auto w-full bg-white text-center">
        <div class="text-2xs leading-3">
            <div
                style="transform: translateX(-50%)"
                class="absolute left-1/2 top-0 h-[1.7cm] w-auto"
            >
                @if (data_get($client, 'logo_small'))
                    <x-flux::print.elements.footer-logo :client="$client" />
                @endif
            </div>
            <div class="w-full">
                <div class="border-semi-black border-t">
                    <address class="float-left text-left not-italic">
                        <x-flux::print.elements.client :client="$client" />
                    </address>
                    @foreach (data_get($client, 'bankConnections', []) as $bankConnection)
                        <div class="float-right pl-3 text-left">
                            <x-flux::print.elements.bank-connection
                                :bank-connection="$bankConnection"
                            />
                        </div>
                        @if (data_get($client, 'logo_small'))
                            @break
                        @endif
                    @endforeach

                    <div class="clear-both"></div>
                </div>
            </div>
        </div>
    </footer>
@endif
