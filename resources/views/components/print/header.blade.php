@props([
    'headerLayout' => null,
    'isPreview' => false,
])

@if ($headerLayout)
    <header
        style="height: {{ data_get($headerLayout, 'height', '1.7') }}cm"
        class="w-full bg-white text-2xs leading-3"
    >
        @foreach (data_get($headerLayout, 'elements', []) as $element)
            {{-- subject --}}
            @if (data_get($element, 'id', '') === 'header-subject')
                <div
                    style="
                        left: {{ data_get($element, 'x', '0') }}cm;
                        top: {{ data_get($element, 'y', '0') }}cm;
                    "
                    class="absolute"
                >
                    <x-flux::print.elements.header-subject
                        :subject="$subject ?? ''"
                    />
                </div>
            @endif

            {{-- logo --}}
            @if (data_get($element, 'id', '') === 'header-logo' && data_get($client, 'logo_small'))
                <div
                    style="
                        top: {{ data_get($element, 'y', '0') }}cm;
                        left: {{ data_get($element, 'x', '0') }}cm;
                        height: {{ data_get($element, 'height', '1.7') }}cm;
                        width: {{ is_null(data_get($element, 'width')) ? 'auto' : data_get($element, 'width', '') . 'cm' }};
                    "
                    class="absolute"
                >
                    <x-flux::print.elements.header-logo :client="$client" />
                </div>
            @endif

            {{-- page count --}}
            @if (data_get($element, 'id', '') === 'header-page-count')
                <div
                    style="
                        left: {{ data_get($element, 'x', '0') }}cm;
                        top: {{ data_get($element, 'y', '0') }}cm;
                    "
                    class="absolute"
                >
                    <x-flux::print.elements.header-page-count
                        :preview="$isPreview"
                    />
                </div>
            @endif
        @endforeach

        {{-- media --}}
        @if (data_get($headerLayout, 'media'))
            @foreach (data_get($headerLayout, 'media', []) as $media)
                <x-flux::print.elements.media :media="$media" />
            @endforeach
        @endif

        {{-- snippets --}}
        @if (data_get($headerLayout, 'snippets'))
            @foreach (data_get($headerLayout, 'snippets', []) as $snippet)
                <x-flux::print.elements.snippet :snippet="$snippet" />
            @endforeach
        @endif
    </header>
@else
    {{-- default header if no changes are made --}}
    <header class="h-auto w-full bg-white text-center text-2xs leading-3">
        <div class="float-left inline-block text-left">
            <x-flux::print.elements.header-subject :subject="$subject ?? ''" />
            <x-flux::print.elements.header-page-count :preview="$isPreview" />
        </div>
        <div class="float-right inline-block h-[1.7cm] w-auto">
            @if (data_get($client, 'logo_small'))
                <x-flux::print.elements.header-logo :client="$client" />
            @endif
        </div>
    </header>
@endif
