<div
    x-data="apexCharts($wire)"
    class="flex h-full max-h-full w-full flex-col gap-4 pb-4 pt-4 text-sm"
>
    @if ($withSpinner)
        <x-flux::spinner />
    @endif

    <div class="flex w-full flex-row items-center justify-between gap-2">
        <div class="min-w-0 flex-1">
            @section('title')
            @if ($this->showTitle())
                <div class="flex w-full pl-6 pr-2">
                    <h2 class="truncate text-lg font-semibold text-gray-400">
                        {{ $this->getLabel() }}
                    </h2>
                </div>
            @endif
        </div>
        @show
        <div class="flex items-center gap-4">
            @section('options')
            @if ($this instanceof \FluxErp\Contracts\HasWidgetOptions)
                <div class="flex-none">
                    <x-dropdown
                        icon="ellipsis-vertical"
                        static
                        x-on:open="
                            $el.closest('.grid-stack-item-content').style.overflow = $event.detail.status ? 'visible' : 'hidden';
                            if ($event.detail.status) await loadWidgetOptions();
                        "
                    >
                        <div class="max-h-60 overflow-y-auto">
                            <template
                                x-for="option in widgetOptions"
                                x-bind:key="option.label"
                            >
                                <button
                                    type="button"
                                    role="menuitem"
                                    tabindex="0"
                                    class="focus:outline-hidden flex w-full cursor-pointer items-center whitespace-nowrap px-4 py-2 text-sm text-secondary-600 transition-colors duration-150 hover:bg-gray-100 focus:bg-gray-100 dark:text-dark-300 dark:hover:bg-dark-600 dark:focus:bg-dark-600"
                                    x-on:click="
                                        $wire.call(option.method, option.params ?? [])
                                        $refs.dropdown.dispatchEvent(new CustomEvent('select'))
                                    "
                                    x-text="option.label"
                                ></button>
                            </template>
                        </div>
                    </x-dropdown>
                </div>
            @endif

            @show
        </div>
    </div>
    <hr class="mx-6" />
    <div
        class="flex h-full flex-1 flex-grow flex-col justify-between gap-4 dark:text-gray-400"
    >
        @section('chart')
        <div class="chart h-full w-full"></div>
        @show
    </div>
</div>
