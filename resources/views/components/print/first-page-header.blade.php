@props([
    'client',
    'firstPageHeaderLayout',
])

@if($firstPageHeaderLayout)
<div
    style="height:{{$firstPageHeaderLayout['height']}}cm"
    class="relative w-full bg-purple-100">
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
    @endforeach
</div>
@else
<div class="cover-page z-10 h-auto overflow-auto bg-white">
    <div class="grid h-32 content-center">
        <div class="m-auto max-h-72 text-center">
            <x-flux::print.elements.first-page-header-client-name :client="$client" />
        </div>
    </div>
    <table class="w-full">
        <tr>
            <td colspan="2" class="w-full pb-1 pt-6 text-2xs">
                <x-flux::print.elements.first-page-header-address-one-line :client="$client" />
            </td>
        </tr>
        <tr class="h-4">
            <td colspan="2"></td>
        </tr>
        <tr>
            <td class="w-1/2 align-top">
                @if ($slot->isNotEmpty())
                    {!! $slot !!}
                @else
{{--                    <x-flux::print.elements.first-page-header-address :client="$address" />--}}
                @endif
            </td>
            <td class="w-1/2 align-top">
                <div class="float-right inline-block max-w-full text-xs">
                    {{ $rightBlock ?? '' }}
                </div>
            </td>
        </tr>
    </table>
   <x-flux::print.elements.first-page-header-subject :subject="$subject" />
</div>
@endif
