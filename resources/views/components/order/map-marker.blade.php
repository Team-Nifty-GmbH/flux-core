@props(['count' => 1])

<div class="relative">
    <div
        class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-500 text-white shadow-lg"
    >
        <x-icon name="document-text" class="h-5 w-5" />
    </div>
    @if ($count > 1)
        <div
            class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-xs font-bold text-white shadow"
        >
            {{ $count }}
        </div>
    @endif
</div>
