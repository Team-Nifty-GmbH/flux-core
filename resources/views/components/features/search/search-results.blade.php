<div>
    <div
        x-show="$wire.search.length && ! Object.keys($wire.return).length"
        x-cloak
        class="flex w-full items-center justify-center py-4"
    >
        <x-icon name="magnifying-glass" class="mr-2 h-5 w-5" />
        <div>
            {{ __('No results…') }}
        </div>
    </div>
    <x-flux::spinner />
    <ul>
        <template x-for="(items, model) in $wire.return">
            <li>
                <div
                    class="w-full bg-gray-100 px-4 py-2.5 text-xs font-semibold text-gray-900 dark:bg-secondary-600 dark:text-gray-50"
                    x-text="$wire.modelLabels[model].label"
                ></div>
                <ul class="mt-2 text-sm text-gray-800" role="none">
                    <template x-for="item in items">
                        <li
                            class="flex cursor-pointer select-none items-center space-x-1.5 px-4 py-2 hover:bg-indigo-600 hover:text-white"
                            x-on:click="$wire.showDetail(model, item.id)"
                        >
                            <x-avatar
                                image
                                xs
                                x-bind:src="item.src"
                                x-cloak
                                x-show="item.src"
                            />
                            <div
                                class="inline-block align-middle hover:text-white dark:text-gray-50"
                                x-text="item.label"
                            ></div>
                        </li>
                    </template>
                </ul>
            </li>
        </template>
    </ul>
</div>
