@props([
    'headerLayout' => null,
])

@if($headerLayout)
    <header
        style="height: {{ $headerLayout['height'] ?? '1.7' }}cm"
        class="relative w-full bg-purple-200 font-light">
        @foreach($headerLayout['elements'] as $element)
            @if($element['id'] === 'header-subject')
               <div
                    style="left: {{ $element['x'] }}cm;
                        top: {{ $element['y'] }}cm;"
                    class="absolute"
               >
                    <x-flux::print.elements.header-subject :subject="$subject ?? ''" />
               </div>
                @endif
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
        @endforeach
    </header>
@else
<header class="h-auto w-full bg-white text-center font-light">
    <div class="header-content">
        <div>
                <div class="float-left inline-block text-left">
                    <x-flux::print.elements.header-subject :subject="$subject ?? ''" />
                    <div class="page-count text-xs"></div>
                </div>
                <div class="float-right inline-block max-h-72 w-44 text-right">
                    @if($client->logo_small)
                        <x-flux::print.elements.header-logo :client="$client" />
                    @endif
                </div>
            <div class="clear-both"></div>
        </div>
    </div>
</header>
@endif
