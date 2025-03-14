<li
    {{ $attributes->merge(['class' => 'py-4 flex']) }}
>
    <div class="mx-3 w-full">
        <div class="text-sm text-gray-500">{{ $slot }}</div>
    </div>
</li>
