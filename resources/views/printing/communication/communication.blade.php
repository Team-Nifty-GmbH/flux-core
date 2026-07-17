<x-flux::print.first-page-header>
    {!! nl2br(implode('<br>', $model->to)) !!}
    <x-slot:right-block>
        <div style="display: inline-block">
            @section('first-page-right-block')
                <div style="font-size: 12px">
                    {{ ($model->date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
                </div>
            @show
        </div>
    </x-slot:right-block>
</x-flux::print.first-page-header>
<main style="padding-top: 32px">
    <div>{!! $model->html_body ?? $model->text_body !!}</div>
</main>
