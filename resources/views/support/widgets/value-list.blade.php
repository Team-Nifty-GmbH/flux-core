<div class="flex flex-col p-6 gap-4 h-full">
    <x-flux::spinner />
    <div>
        <h2 class="truncate text-lg font-semibold text-gray-400">{{ __($this->title()) }}</h2>
        <hr>
    </div>
    <div class="grid grid-cols-[1fr_auto_auto] gap-4">
        @forelse($items as $item)
            <div class="overflow-hidden truncate">{{ data_get($item, 'label') }}</div>
            <span class="font-bold whitespace-nowrap text-right">{{ data_get($item, 'value') }}</span>
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
        @empty
            <div class="flex w-full h-full items-center justify-center text-gray-400">
                <h2 class="text-2xl font-medium">{{ __('No data available') }}</h2>
            </div>
        @endforelse
    </div>
</div>
