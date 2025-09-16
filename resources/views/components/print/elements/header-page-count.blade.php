@props([
    'preview' => false,
])

<div class="text-xs page-count"> {{ $preview ? 'Page 1 of 1' : '' }}</div>
