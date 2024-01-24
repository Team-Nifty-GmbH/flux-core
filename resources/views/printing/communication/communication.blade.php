<x-layouts.print>
    <x-print.first-page-header />
    <main>
        <div>
            {!! $model->html_body ?? $model->text_body !!}
        </div>
    </main>
</x-layouts.print>
