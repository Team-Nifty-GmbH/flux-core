@props([
    'client',
    'firstPageHeaderLayout',
    'model',
    'address',
    'subject',
])

<div
    draggable="false"
    style="height: {{ data_get($firstPageHeaderLayout, 'height', '5') }}cm"
    class="first-page-header-margin-top relative w-full bg-white text-2xs leading-3"
>
    {{-- elements --}}
    @foreach (data_get($firstPageHeaderLayout, 'elements', []) as $element)
        @if (data_get($element, 'id', '') === 'first-page-header-subject')
            <div
                style="
                    left: {{ data_get($element, 'x', '0') }}cm;
                    top: {{ data_get($element, 'y', '0') }}cm;
                "
                class="absolute"
            >
                <x-flux::print.elements.first-page-header-subject
                    :subject="$subject"
                />
            </div>
        @endif

        @if (data_get($element, 'id', '') === 'first-page-header-postal-address-one-line')
            <div
                style="
                    left: {{ data_get($element, 'x', '0') }}cm;
                    top: {{ data_get($element, 'y', '0') }}cm;
                "
                class="absolute text-2xs"
            >
                <x-flux::print.elements.first-page-header-address-one-line
                    :client="$client"
                />
            </div>
        @endif

        @if (data_get($element, 'id', '') === 'first-page-header-client-name')
            <div
                style="
                    left: {{ data_get($element, 'x', '0') }}cm;
                    top: {{ data_get($element, 'y', '0') }}cm;
                "
                class="absolute"
            >
                <x-flux::print.elements.first-page-header-client-name
                    :client="$client"
                />
            </div>
        @endif

        @if (data_get($element, 'id', '0') === 'first-page-header-right-block')
            <div
                style="
                    left: {{ data_get($element, 'x', '0') }}cm;
                    top: {{ data_get($element, 'y', '0') }}cm;
                "
                class="absolute"
            >
                <x-flux::print.elements.first-page-header-right-block-order
                    :model="$model"
                />
            </div>
        @endif

        @if (data_get($element, 'id', '0') === 'first-page-header-final-invoice')
            <div
                style="
                    left: {{ data_get($element, 'x', '0') }}cm;
                    top: {{ data_get($element, 'y', '0') }}cm;
                "
                class="absolute"
            >
                <x-flux::print.elements.first-page-header-final-invoice
                    :model="$model"
                />
            </div>
        @endif

        @if (data_get($element, 'id', '0') === 'first-page-header-refund')
            <div
                style="
                    left: {{ data_get($element, 'x', '0') }}cm;
                    top: {{ data_get($element, 'y', '0') }}cm;
                "
                class="absolute"
            >
                <x-flux::print.elements.first-page-header-refund
                    :model="$model"
                />
            </div>
        @endif

        @if (data_get($element, 'id', '') === 'first-page-header-address' && isset($address))
            <div
                style="
                    left: {{ data_get($element, 'x', '0') }}cm;
                    top: {{ data_get($element, 'y', '0') }}cm;
                "
                class="absolute"
            >
                <x-flux::print.elements.first-page-header-address
                    :address="$address"
                />
            </div>
        @endif
    @endforeach

    {{-- media --}}
    @if (data_get($firstPageHeaderLayout, 'media'))
        @foreach (data_get($firstPageHeaderLayout, 'media', []) as $media)
            <x-flux::print.elements.media :media="$media" />
        @endforeach
    @endif

    {{-- snippets --}}
    @if (data_get($firstPageHeaderLayout, 'snippets'))
        @foreach (data_get($firstPageHeaderLayout, 'snippets', []) as $snippet)
            <x-flux::print.elements.snippet :snippet="$snippet" />
        @endforeach
    @endif
</div>
