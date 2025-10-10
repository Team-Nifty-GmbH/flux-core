@props([
    'snippet',
])
@use(FluxErp\Models\PrintLayoutSnippet)
<div
    style="
        height: {{ data_get($snippet, 'height', '1.7') }}cm;
        width: {{ data_get($snippet, 'width', '10') }}cm;
        left: {{ data_get($snippet, 'x', '0') }}cm;
        top: {{ data_get($snippet, 'y', '0') }}cm;
    "
    class="absolute"
>
    {!! resolve_static(PrintLayoutSnippet::class, 'query')->whereKey(data_get($snippet, 'id'))->first()?->content ?? '' !!}
</div>
