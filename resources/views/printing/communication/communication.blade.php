<x-flux::print.first-page-header>
    {!! nl2br(implode('<br>', $model->to)) !!}
    <x-slot:right-block>
        <div class="inline-block">
            @section('first-page-right-block')
            <div class="text-xs">
                {{ ($model->date ?: now())->locale(app()->getLocale())->isoFormat('L') }}
            </div>
            @show
        </div>
    </x-slot>
</x-flux::print.first-page-header>
<main class="pt-8">
    <div>
        {!! $model->html_body ?? $model->text_body !!}
    </div>
</main>
