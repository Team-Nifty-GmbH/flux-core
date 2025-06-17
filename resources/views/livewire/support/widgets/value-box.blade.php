<div class="relative flex h-80 w-full flex-col justify-between gap-1 p-6">
    <x-flux::spinner />
    <div class="flex w-full items-center justify-between">
        <div class="flex w-full items-center justify-between overflow-hidden">
            <div class="flex flex-col">
                <div class="truncate text-lg font-semibold text-gray-500">
                    {{ __($this->title()) }}
                </div>
                <div
                    class="text-3xl font-semibold text-gray-900"
                    x-text="$wire.sum"
                ></div>
            </div>

            <div class="flex items-center gap-2">
                <x-icon
                    :name="$this->icon()"
                    class="text-primary-500 h-12 w-12"
                />
                @if ($this instanceof \FluxErp\Contracts\HasWidgetOptions)
                    <div class="flex-none">
                        <x-dropdown icon="ellipsis-vertical" static>
                            @foreach ($this->options() as $option)
                                <x-dropdown.items
                                    :text="data_get($option, 'label')"
                                    wire:click="{{ data_get($option, 'method') }}"
                                />
                            @endforeach
                        </x-dropdown>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-1 flex flex-col">
        <template x-if="$wire.growthRate !== null">
            <div>
                @if ($shouldBePositive)
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
                @else
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
                @endif
            </div>
        </template>

        <span
            class="text-lg font-semibold text-gray-600"
            x-html="$wire.subValue"
        ></span>

        <span
            class="mt-2 text-base text-gray-400"
            x-cloak
            x-show="$wire.previousSum"
            x-text="'{{ __('Previous Period') }} ' + $wire.previousSum"
        ></span>
    </div>
</div>
