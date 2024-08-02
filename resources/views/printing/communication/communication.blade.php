<x-print.first-page-header>
    {!! nl2br(implode('<br>', $model->to)) !!}
</x-print.first-page-header>
<main>
    <div>
        {!! $model->html_body ?? $model->text_body !!}
    </div>
</main>
