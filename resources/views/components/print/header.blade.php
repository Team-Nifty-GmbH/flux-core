@props([
    'headerLayout' => null,
    'isPreview' => false,
])

@if($headerLayout)
    <header
        style="height: {{ $headerLayout['height'] ?? '1.7' }}cm;"
        class="w-full bg-white text-2xs leading-3">
        @foreach($headerLayout['elements'] as $element)
            {{--  subject    --}}
            @if($element['id'] === 'header-subject')
               <div
                    style="left: {{ $element['x'] }}cm;
                        top: {{ $element['y'] }}cm;"
                    class="absolute"
               >
                    <x-flux::print.elements.header-subject :subject="$subject ?? ''" />
               </div>
                @endif
            {{--  logo    --}}
                @if($element['id'] === 'header-logo' && $client->logo_small)
                <div
                    style="top: {{ $element['y'] }}cm;
                    left: {{ $element['x'] }}cm;
                    height: {{ $element['height'] ?? 1.7 }}cm;
                    width: {{ is_null($element['width']) ? 'auto' : $element['width'] . 'cm'}};"
                    class="absolute">
                    <x-flux::print.elements.header-logo :client="$client" />
                </div>
                @endif
            {{--  page count    --}}
            @if($element['id'] === 'header-page-count')
                <div
                    style="left: {{ $element['x'] }}cm;
                        top: {{ $element['y'] }}cm;"
                    class="absolute"
                >
                    <x-flux::print.elements.header-page-count :preview="$isPreview" />
                </div>
            @endif
        @endforeach
            {{--  media    --}}
            @if($headerLayout['media'])
                @foreach($headerLayout['media'] as $media)
                    <x-flux::print.elements.media :media="$media" />
                @endforeach
            @endif
        {{--  snippets    --}}
        @if($headerLayout['snippets'])
            @foreach($headerLayout['snippets'] as $snippet)
                <x-flux::print.elements.snippet :snippet="$snippet" />
            @endforeach
        @endif
    </header>
@else
{{-- default header if no changes are made--}}
<header class="h-auto w-full bg-white text-center text-2xs leading-3">
    <div class="float-left inline-block text-left">
        <x-flux::print.elements.header-subject :subject="$subject ?? ''" />
        <x-flux::print.elements.header-page-count :preview="$isPreview" />
    </div>
    <div class="float-right inline-block w-auto h-[1.7cm]">
        @if($client->logo_small)
            <x-flux::print.elements.header-logo :client="$client" />
        @endif
    </div>
</header>
@endif
