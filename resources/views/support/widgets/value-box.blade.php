<div class="flex p-6 gap-6 h-full w-full">
    <x-flux::spinner />
    <div class="flex flex-col justify-center">
        <x-icon :name="$this->icon()" class="w-12 h-12 text-primary-500" />
    </div>
    <div class="flex flex-col justify-between w-full grow">
        <div class="flex justify-between w-full">
            <h2 class="truncate text-lg font-semibold text-gray-400">{{ __($this->title()) }}</h2>
            @if(class_implements($this, \FluxErp\Contracts\HasWidgetOptions::class))
                <x-dropdown>
                    @foreach($this->options() as $option)
                        <x-dropdown.item :label="data_get($option, 'label')" wire:click="{{ data_get($option, 'method') }}">
                        </x-dropdown.item>
                    @endforeach
                </x-dropdown>
            @endif
        </div>
        <div>
            <div class="grow flex flex-wrap gap-4 max-w-full items-center">
                <div class="font-bold text-2xl whitespace-nowrap truncate flex-none" x-text="$wire.sum">
                </div>
                @if($shouldBePositive)
                    <template x-if="$wire.growthRate !== null">
                        <div>
                            <x-badge
                                x-cloak
                                x-show="$wire.growthRate > 0"
                                positive
                                lg
                            >
                                <x-slot:prepend>
                                    <i class="ph ph-caret-up"></i>
                                </x-slot:prepend>
                                <span x-text="$wire.growthRate + '%'">
                                </span>
                            </x-badge>
                            <x-badge
                                x-cloak
                                x-show="$wire.growthRate < 0"
                                negative
                                lg
                            >
                                <x-slot:prepend>
                                    <i class="ph ph-caret-down"></i>
                                </x-slot:prepend>
                                <span x-text="$wire.growthRate + '%'">
                                </span>
                            </x-badge>
                        </div>
                    </template>
                @else
                    <template x-if="$wire.growthRate !== null">
                        <div>
                            <x-badge
                                x-cloak
                                x-show="$wire.growthRate > 0"
                                negative
                                lg
                            >
                                <x-slot:prepend>
                                    <i class="ph ph-caret-up"></i>
                                </x-slot:prepend>
                                <span x-text="$wire.growthRate + '%'">
                                </span>
                            </x-badge>
                            <x-badge
                                x-cloak
                                x-show="$wire.growthRate < 0"
                                positive
                                lg
                            >
                                <x-slot:prepend>
                                    <i class="ph ph-caret-down"></i>
                                </x-slot:prepend>
                                <span x-text="$wire.growthRate + '%'">
                                </span>
                            </x-badge>
                        </div>
                    </template>
                @endif
            </div>
            <div class="text-lg font-semibold whitespace-nowrap truncate flex-none" x-html="$wire.subValue">
            </div>

        </div>
        <div class="min-h-6">
            <span class="text-gray-400" x-cloak x-show="$wire.previousSum" x-text="'{{ __('Previous Period') }} ' + $wire.previousSum">
            </span>
        </div>
    </div>
</div>
