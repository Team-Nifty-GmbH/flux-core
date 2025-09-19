@props([
    'subject' => null,
])

<h1
    draggable="false"
    class="text-xl font-semibold">
    {{ $subject ?? '' }}
</h1>
