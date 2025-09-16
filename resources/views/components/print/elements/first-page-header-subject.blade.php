@props([
    'subject' => null,
])

<h1 class="text-xl font-semibold">
    {{ $subject ?? '' }}
</h1>
