<div
    {{
        $attributes->merge([
            "class" => "cursor-pointer w-full border-transparent text-gray-700 hover:bg-gray-50 hover:text-gray-900 group flex items-center px-3 py-2 text-sm font-medium border-l-4",
        ])
    }}
>
    {{ $slot }}
</div>
