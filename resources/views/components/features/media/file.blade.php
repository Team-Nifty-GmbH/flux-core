<div
    {{ $attributes->merge(['class' => 'py-3 flex items-center justify-between text-sm']) }}
>
    <div class="flex w-0 flex-1 items-center">
        <x-icon name="paper-clip" class="h-4 w-4" />
        <span class="w-0 flex-1 truncate">
            {{ $slug ?? '/' }}
        </span>
        <span class="w-0 flex-1 truncate">
            {{ $filename }}
        </span>
    </div>
    <div class="flex flex-shrink-0 space-x-4">
        {{ $buttons ?? '' }}
    </div>
</div>
