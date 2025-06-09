<div
    x-data="apexCharts($wire)"
    class="flex h-full max-h-full w-full flex-col gap-4 pb-4 pt-4 text-sm"
>
    @if ($withSpinner)
        <x-flux::spinner />
    @endif

    <div class="flex items-center justify-between gap-4">
        <div>
            @section('title')
            @if ($this->showTitle())
                <div class="flex w-full justify-between px-6">
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
                    <x-dropdown icon="ellipsis-vertical" static>
                        @foreach ($this->options() as $option)
                            <x-dropdown.items
                                :text="data_get($option, 'label')"
                                wire:click="{{ data_get($option, 'method') }}({{ json_encode(data_get($option, 'params', [])) }})"
                            />
                        @endforeach
                    </x-dropdown>
                </div>
            @endif

            @show
        </div>
    </div>
    <hr class="mx-6" />
    <div
        class="flex h-full flex-1 grow flex-col justify-between gap-4 dark:text-gray-400"
    >
        @section('chart')
        <div class="chart h-full w-full"></div>
        @show
    </div>
</div>
