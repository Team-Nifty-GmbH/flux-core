<x-dynamic-component :component="$noLayout != true ? 'flux::layouts.print.index' : 'layouts.empty'">
<style>
    page-break {
        break-before: page;
    }
</style>
<script src="https://unpkg.com/pagedjs/dist/paged.polyfill.js"></script>
@foreach($printData as $printable)
    @if(view()->exists($printable->view))
        @php
            $bladeData = json_decode(json_encode($printable->data), true);
            $bladeData['model'] = $printable;
        @endphp
        {{ view($printable->view, $bladeData) }}
        @if($printData->count() > 1 && $loop->iteration < $printData->count())
            <page-break></page-break>
        @endif
    @endif
@endforeach
</x-dynamic-component>
