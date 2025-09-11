@props(['snippet'])
@use(FluxErp\Models\PrintLayoutSnippet)
<div
    style="height: {{$snippet['height'] ?? '1.7'}}cm; width: {{$snippet['width'] ?? '10'}}cm; left: {{$snippet['x']}}cm; top: {{$snippet['y']}}cm;"
    class="absolute"
>
    {!! resolve_static(PrintLayoutSnippet::class,'query')->whereKey($snippet['id'])->first()?->content ?? '' !!}
</div>
