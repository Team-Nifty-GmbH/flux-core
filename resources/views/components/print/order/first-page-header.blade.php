@props([
    'client',
    'firstPageHeaderLayout',
    'model',
    'address'
])

@if($firstPageHeaderLayout)
    <div
        draggable="false"
        style="height:{{$firstPageHeaderLayout['height']}}cm;"
        class="relative w-full bg-white first-page-header-margin-top">
        {{--      elements--}}
        @foreach($firstPageHeaderLayout['elements'] as $element)
            @if($element['id'] === 'first-page-header-subject')
                <div
                    style="left: {{ $element['x'] }}cm;
                        top: {{ $element['y'] }}cm;"
                    class="absolute">
                    <x-flux::print.elements.first-page-header-subject :subject="$subject" />
                </div>
            @endif
            @if($element['id'] === 'first-page-header-postal-address-one-line')
                <div
                    style="left: {{ $element['x'] }}cm;
                        top: {{ $element['y'] }}cm;"
                    class="absolute">
                    <x-flux::print.elements.first-page-header-address-one-line :client="$client" />
                </div>
            @endif
            @if($element['id'] === 'first-page-header-client-name')
                <div
                    style="left: {{ $element['x'] }}cm;
                        top: {{ $element['y'] }}cm;"
                    class="absolute">
                    <x-flux::print.elements.first-page-header-client-name :client="$client" />
                </div>
            @endif
            @if($element['id'] === 'first-page-header-right-block')
                <div
                    style="left: {{ $element['x'] }}cm;
                        top: {{ $element['y'] }}cm;"
                    class="absolute">
                    <x-flux::print.elements.first-page-header-right-block-order :model="$model" />
                </div>
            @endif
            @if($element['id'] === 'first-page-header-address' && isset($address))
                <div
                    style="left: {{ $element['x'] }}cm;
                        top: {{ $element['y'] }}cm;"
                    class="absolute">
                    <x-flux::print.elements.first-page-header-address :address="$address" />
                </div>
            @endif
        @endforeach
        {{--      media--}}
        @if($firstPageHeaderLayout['media'])
            @foreach($firstPageHeaderLayout['media'] as $media)
                <x-flux::print.elements.media :media="$media" />
            @endforeach
        @endif
        {{--      snippets--}}
        @if($firstPageHeaderLayout['snippets'])
            @foreach($firstPageHeaderLayout['snippets'] as $snippet)
                <x-flux::print.elements.snippet :snippet="$snippet" />
            @endforeach
        @endif
    </div>
@else
    <div class="cover-page z-10 h-auto overflow-auto bg-white first-page-header-margin-top">
        <div class="grid h-32 content-center">
            <div class="m-auto max-h-72 text-center">
                <x-flux::print.elements.first-page-header-client-name :client="$client" />
            </div>
        </div>
        <table class="w-full">
            <tr>
                <td class="pb-1 pt-6 text-2xs">
                    <span class="inline-block">
                        <x-flux::print.elements.first-page-header-address-one-line :client="$client" />
                    </span>
                </td>
            </tr>
            <tr class="h-4">
                <td colspan="2"></td>
            </tr>
            <tr>
                <td class="w-1/2 align-top">
                        @isset($address)
                            <x-flux::print.elements.first-page-header-address :address="$address" />
                       @else
                            <x-flux::print.elements.first-page-header-right-block-order :model="$model" />
                       @endisset
                </td>
            </tr>
        </table>
        <x-flux::print.elements.first-page-header-subject :subject="$subject" />
    </div>
@endif
