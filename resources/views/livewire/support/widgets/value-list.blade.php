<div class="flex h-full flex-col gap-4 p-6">
    <div>
        <h2 class="truncate text-lg font-semibold text-gray-400">
            {{ __($this->title()) }}
        </h2>
        <hr />
    </div>
    <div class="overflow-auto">
        <table class="w-full">
            <tbody>
                <template x-for="item in $wire.items">
                    <tr>
                        <td class="flex flex-col pb-2 pr-1.5">
                            <div x-html="item.label ?? null"></div>
                            <div
                                x-html="item.subLabel ?? null"
                                class="text-sm text-gray-400"
                            ></div>
                        </td>
                        <td
                            class="whitespace-nowrap pb-2 pr-1.5 text-right font-bold"
                            x-text="item.value"
                        ></td>
                        <td class="pb-2 text-right">
                            @if ($shouldBePositive)
                                <template
                                    x-if="item.growthRate !== null && ! isNaN(item.growthRate)"
                                >
                                    <div>
                                        <x-badge
                                            x-cloak
                                            x-show="item.growthRate > 0"
                                            class="w-full"
                                            color="emerald"
                                        >
                                            <x-slot:left>
                                                <i class="ph ph-caret-up"></i>
                                            </x-slot>
                                            <span
                                                x-text="item.growthRate + '%'"
                                            ></span>
                                        </x-badge>
                                        <x-badge
                                            x-cloak
                                            x-show="item.growthRate < 0"
                                            class="w-full"
                                            color="red"
                                        >
                                            <x-slot:left>
                                                <i class="ph ph-caret-down"></i>
                                            </x-slot>
                                            <span
                                                x-text="item.growthRate + '%'"
                                            ></span>
                                        </x-badge>
                                    </div>
                                </template>
                            @else
                                <template
                                    x-if="item.growthRate !== null && ! isNaN(item.growthRate)"
                                >
                                    <div>
                                        <x-badge
                                            x-cloak
                                            x-show="item.growthRate > 0"
                                            class="w-full"
                                            color="red"
                                        >
                                            <x-slot:left>
                                                <i class="ph ph-caret-up"></i>
                                            </x-slot>
                                            <span
                                                x-text="item.growthRate + '%'"
                                            ></span>
                                        </x-badge>
                                        <x-badge
                                            x-cloak
                                            x-show="item.growthRate < 0"
                                            class="w-full"
                                            color="emerald"
                                        >
                                            <x-slot:left>
                                                <i class="ph ph-caret-down"></i>
                                            </x-slot>
                                            <span
                                                x-text="item.growthRate + '%'"
                                            ></span>
                                        </x-badge>
                                    </div>
                                </template>
                            @endif
                            <x-badge
                                x-cloak
                                x-show="item.growthRate == 0"
                                color="gray"
                                class="w-full"
                            >
                                <x-slot:left>
                                    <i class="ph ph-caret-right"></i>
                                </x-slot>
                                <span>{{ __('New') }}</span>
                            </x-badge>
                            <template x-if="isNaN(item.growthRate)">
                                <div x-html="item.growthRate ?? null"></div>
                            </template>
                        </td>
                    </tr>
                </template>
                <template x-if="$wire.items.length === 0">
                    <tr>
                        <td colspan="3" class="p-4 text-center text-gray-400">
                            <h2 class="text-2xl font-medium">
                                {{ __('No data available') }}
                            </h2>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
        @if ($this->hasLoadMore())
            <div
                class="mt-4 flex w-full justify-center"
                x-cloak
                x-show="await $wire.hasMore()"
            >
                <x-button
                    color="secondary"
                    light
                    loading="showMore"
                    :text="__('Load more')"
                    wire:click="showMore()"
                />
            </div>
        @endif
    </div>
</div>
