<ul role="list" {{ $attributes->merge(['class' => 'divide-y divide-gray-200 max-h-96 overflow-auto dark:divide-secondary-700']) }}>
    {{ $slot }}
</ul>
