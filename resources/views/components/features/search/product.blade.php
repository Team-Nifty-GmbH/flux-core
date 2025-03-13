<div
    {{ $attributes }}
    class="flex cursor-pointer space-x-2 px-10 py-2 hover:bg-blue-50 dark:hover:bg-secondary-800"
>
    <div class="font-bold" x-text="result.product_number"></div>
    <div x-text="result.name"></div>
    <div x-text="result.lastname"></div>
</div>
