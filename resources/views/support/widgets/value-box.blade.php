<div class="flex h-full w-full flex-col">
    <div class="px-6 pt-6">
        <h2 class="flex items-center justify-between truncate text-lg font-semibold text-gray-400">
            <span>{{ __($this->title()) }}</span>
            @if (class_implements($this, \FluxErp\Contracts\HasWidgetOptions::class))
                <x-dropdown icon="ellipsis-vertical" static>
                    @foreach ($this->options() as $option)
                        <x-dropdown.items
                            :text="data_get($option, 'label')"
                            wire:click="{{ data_get($option, 'method') }}"
                        />
                    @endforeach
                </x-dropdown>
            @endif
        </h2>
        <hr />
    </div>
    <div class="flex h-full w-full gap-6 px-6 pb-6 items-center">
        <x-flux::spinner />
        <div class="flex w-full grow flex-col justify-between">
            <div>
                <div class="flex max-w-full grow flex-wrap items-center justify-center gap-4">
                    <div class="flex items-center justify-center">
                        <x-icon :name="$this->icon()" class="text-primary-500 h-12 w-12" />
                        <div
                            class="ml-4 flex-none truncate whitespace-nowrap text-2xl font-bold"
                            x-text="$wire.sum"
                        ></div>
                    </div>
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
                    class="flex-none truncate whitespace-nowrap text-lg font-semibold text-center"
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
</div>
