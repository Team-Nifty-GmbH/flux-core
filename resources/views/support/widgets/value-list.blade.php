<div class="flex flex-col p-6 gap-4 h-full">
    <x-flux::spinner />
    <h2 class="truncate text-lg font-semibold text-gray-400">{{ __($this->title()) }}</h2>
    <hr>
    <div class="flex flex-col gap-3 h-full justify-between">
        @forelse($items as $item)
            <div class="flex gap-2 justify-between">
                <div class="overflow-hidden truncate">{{ data_get($item, 'label') }}</div>
                <div class="flex gap-4">
                    <span class="font-bold whitespace-nowrap">{{ data_get($item, 'value') }}</span>
                    @if(! is_null($growthRate = data_get($item, 'growthRate')))
                        @if($shouldBePositive)
                            <x-badge
                                :icon="$growthRate > 0 ? 'chevron-up' : ($growthRate < 0 ? 'chevron-down' : 'chevron-right')"
                                :color="$growthRate > 0 ? 'positive' : ($growthRate < 0 ? 'negative' : 'secondary')"
                            >
                                {{ $growthRate }}%
                            </x-badge>
                        @else
                            <x-badge
                                :icon="$growthRate > 0 ? 'chevron-up' : ($growthRate < 0 ? 'chevron-down' : 'chevron-right')"
                                :color="$growthRate < 0 ? 'positive' : ($growthRate > 0 ? 'negative' : 'secondary')"
                            >
                                {{ $growthRate }}%
                            </x-badge>
                        @endif
                    @endif
                </div>
            </div>
        @empty
            <div class="flex w-full h-full items-center justify-center text-gray-400">
                <h2 class="text-2xl font-medium">{{ __('No data available') }}</h2>
            </div>
        @endforelse
    </div>
</div>
