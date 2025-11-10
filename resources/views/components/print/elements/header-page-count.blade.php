@props([
    'preview' => false,
])

@if (! $preview)
    <div>{{ __('Page') }} 1 {{ __('of') }} 1</div>
@else
    <div class="font-light">
        {{ __('Page') }}
        <span class="page-count"></span>
        {{ __('of') }} DOMPDF_PAGE_COUNT_PLACEHOLDER
    </div>
@endif
