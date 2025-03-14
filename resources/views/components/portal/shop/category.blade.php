@props([
    'category',
    'level' => 0,
    'path' => $path??false?'null':$category->id,
])
<div
    class="cursor-pointer whitespace-nowrap"
    style="padding-left: {{ $level * 10 }}px"
    @if($level > 0) x-cloak x-show="open.includes('{{ $path }}')" @endif
>
    <div
        class="flex justify-between gap-1.5"
        x-on:click="selectCategory({{ $category->id ?? 'null' }}, '{{ $path }}')"
        x-bind:class="$wire.$parent.category === {{ $category->id ?? 'null' }} && 'font-bold'"
    >
        <div>{{ $category->name }}</div>
        @if ($category->children_count > 0)
            <x-icon
                name="chevron-left"
                x-bind:class="open.includes('{{ $path }}') && '-rotate-90'"
                class="h-4 w-4 transform"
            />
        @endif
    </div>
    @if ($category->relationLoaded('children'))
        <div class="flex flex-col gap-1.5">
            @foreach ($category->children ?? [] as $child)
                <x-portal.shop.category
                    :path="$path"
                    :level="$level + 1"
                    :category="$child"
                />
            @endforeach
        </div>
    @endif
</div>
