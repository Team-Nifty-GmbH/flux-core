<tr
    {{ $attributes->merge(['class' => 'hover:bg-gray-100 dark:hover:bg-secondary-900 text-sm']) }}
>
    {{ $slot ?? '' }}
</tr>
