@props([
    'client',
    'firstPageHeaderLayout',
    'model',
    'address',
])

@if ($firstPageHeaderLayout)
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
@else
    <div
        class="cover-page first-page-header-margin-top z-10 h-auto overflow-auto bg-white text-2xs leading-3"
    >
        <div class="grid h-32 content-center">
            <div class="m-auto max-h-72 text-center">
                <x-flux::print.elements.first-page-header-client-name
                    :client="$client"
                />
            </div>
        </div>
        <table class="w-full">
            <tr>
                <td class="pb-1 pt-6 text-2xs">
                    <span class="inline-block">
                        <x-flux::print.elements.first-page-header-address-one-line
                            :client="$client"
                        />
                    </span>
                </td>
            </tr>
            <tr class="h-4">
                <td colspan="2"></td>
            </tr>
            <tr>
                <td class="w-1/2 align-top">
                    @isset($address)
                        <x-flux::print.elements.first-page-header-address
                            :address="$address"
                        />
                    @else
                        <x-flux::print.elements.first-page-header-right-block-order
                            :model="$model"
                        />
                    @endisset
                </td>
            </tr>
        </table>
        <x-flux::print.elements.first-page-header-subject
            :subject="$subject"
        />
    </div>
@endif
