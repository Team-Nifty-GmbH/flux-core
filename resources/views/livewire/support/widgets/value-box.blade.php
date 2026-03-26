<div class="flex h-full w-full gap-6 p-6">
    <x-flux::spinner />
    <div class="flex flex-col justify-center">
        <x-icon :name="$this->icon()" class="h-12 w-12 text-primary-500" />
    </div>
    <div class="flex w-full grow flex-col justify-between overflow-hidden">
        <div class="flex w-full justify-between">
            <div class="truncate text-lg font-semibold text-gray-400">
                {{ __($this->title()) }}
            </div>
            @if ($this instanceof \FluxErp\Contracts\HasWidgetOptions)
                <div class="flex-none">
                    <x-dropdown icon="ellipsis-vertical" static>
                        @foreach ($this->options() ?? [] as $option)
                            <x-dropdown.items
                                :text="data_get($option, 'label')"
                                wire:click="{{ data_get($option, 'method') }}('{{ data_get($option, 'params') }}')"
                            />
                        @endforeach
                    </x-dropdown>
                </div>
            @endif
        </div>
        <div>
            <div
                class="flex max-w-full grow flex-wrap items-center gap-4 overflow-hidden"
            >
                <div
                    class="flex-none truncate whitespace-nowrap text-2xl font-bold"
                    x-text="$wire.sum"
                ></div>
                @if ($shouldBePositive)
                    <template x-if="$wire.growthRate !== null">
                        <div>
                            <x-badge
                                x-cloak
                                x-show="$wire.growthRate > 0"
                                color="emerald"
                                lg
                            >
                                <x-slot:left>
                                    <i class="ph ph-caret-up"></i>
                                </x-slot>
                                <span x-text="$wire.growthRate + '%'"></span>
                            </x-badge>
                            <x-badge
                                x-cloak
                                x-show="$wire.growthRate < 0"
                                color="red"
                                lg
                            >
                                <x-slot:left>
                                    <i class="ph ph-caret-down"></i>
                                </x-slot>
                                <span x-text="$wire.growthRate + '%'"></span>
                            </x-badge>
                        </div>
                    </template>
                @else
                    <template x-if="$wire.growthRate !== null">
                        <div>
                            <x-badge
                                x-cloak
                                x-show="$wire.growthRate > 0"
                                color="red"
                                lg
                            >
                                <x-slot:left>
                                    <i class="ph ph-caret-up"></i>
                                </x-slot>
                                <span x-text="$wire.growthRate + '%'"></span>
                            </x-badge>
                            <x-badge
                                x-cloak
                                x-show="$wire.growthRate < 0"
                                color="emerald"
                                lg
                            >
                                <x-slot:left>
                                    <i class="ph ph-caret-down"></i>
                                </x-slot>
                                <span x-text="$wire.growthRate + '%'"></span>
                            </x-badge>
                        </div>
                    </template>
                @endif
            </div>
            <div
                class="flex-none truncate whitespace-nowrap text-lg font-semibold"
                x-html="$wire.subValue"
            ></div>
        </div>
        <div class="min-h-6">
            <span
                class="text-gray-400"
                x-cloak
                x-show="$wire.previousSum"
                x-text="'{{ __('Previous Period') }} ' + $wire.previousSum"
            ></span>
        </div>
    </div>
</div>
