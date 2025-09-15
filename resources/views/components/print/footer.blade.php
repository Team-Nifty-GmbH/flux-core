@props([
    'footerLayout' => null,
])

@php
    $bankConnections = $client->bankConnections;
    $bankIds = array_map(fn($item) => 'footer-bank-' . $item['id'],$bankConnections->toArray() );
@endphp

@if($footerLayout)
<footer
    style="height: {{ $footerLayout['height'] }}cm"
    class="relative w-full bg-violet-200 border-semi-black border-t text-2xs leading-3">
 @foreach($footerLayout['elements'] as $element)
    {{--  client    --}}
    @if($element['id'] === 'footer-client-' . $client['id'] )
        <address
            style="left: {{ $element['x'] }}cm; top: {{ $element['y'] }}cm;"
            class="absolute not-italic">
            <x-flux::print.elements.client  :client="$client" />
        </address>
    @endif
    {{--  logo    --}}
    @if($element['id'] === 'footer-logo' && $client->logo_small)
            <div
                class="absolute"
                style="
                height: {{ $element['height'] ?? '1.7' }}cm ;
                width:{{ is_null($element['width']) ? 'auto' : $element['width'] . 'cm'  }};
                left: {{ $element['x'] }}cm;
                top: {{ $element['y'] }}cm;">
                    <x-flux::print.elements.footer-logo :client="$client" />
                </div>
    @endif
    {{--  bank connection    --}}
    @if(in_array($element['id'], $bankIds))
        @php
            $index = array_search($element['id'], $bankIds);
            $bank = is_numeric($index) ? $bankConnections[$index] : null;
        @endphp
        @if($bank)
            <div
                style="left: {{ $element['x'] }}cm; top: {{ $element['y'] }}cm;"
                class="absolute">
                <x-flux::print.elements.bank-connection :bank-connection="$bank" />
            </div>
        @endif
    @endif
 @endforeach
    {{--  media    --}}
 @if($footerLayout['media'])
     @foreach($footerLayout['media'] as $media)
            <x-flux::print.elements.media :media="$media" />
     @endforeach
 @endif
    {{--  snippets    --}}
 @if($footerLayout['snippets'])
         @foreach($footerLayout['snippets'] as $snippet)
             <x-flux::print.elements.snippet :snippet="$snippet" />
         @endforeach
 @endif
</footer>
@else
{{--  default footer if no layout saved --}}
<footer class="h-auto w-full bg-white text-center">
    <div class="footer-content text-2xs leading-3">
            <div class="absolute left-0 right-0 h-[1.7cm] w-fit m-auto px-6">
                @if($client->logo_small)
                    <x-flux::print.elements.footer-logo :client="$client" />
                @endif
            </div>
        <div class="w-full">
            <div class="border-semi-black border-t">
                    <address class="float-left text-left not-italic">
                        <x-flux::print.elements.client  :client="$client" />
                    </address>
                    @foreach ($client->bankConnections as $bankConnection)
                        <div class="float-right pl-3 text-left">
                            <x-flux::print.elements.bank-connection :bank-connection="$bankConnection" />
                        </div>
                        @if ($client->logo_small)
                            @break
                        @endif
                    @endforeach
                <div class="clear-both"></div>
            </div>
        </div>
    </div>
</footer>
@endif
