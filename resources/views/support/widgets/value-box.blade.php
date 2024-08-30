<div class="flex p-6 gap-6 h-full">
    <x-flux::spinner />
    <div class="flex flex-col justify-center">
        <x-icon :name="$this->icon()" class="w-12 h-12 text-primary-500" />
    </div>
    <div class="flex flex-col justify-between">
        <h2 class="truncate text-lg font-semibold text-gray-400">{{ __($this->title()) }}</h2>
        <div class="flex gap-4 max-w-full">
            <div class="font-bold text-2xl whitespace-nowrap truncate flex-none">
                {{ $sum }}
            </div>
            @if(! is_null($growthRate))
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
        @if(! is_null($previousSum))
            <span class="text-gray-400">
                {{ __('Previous Period') }} {{ $previousSum }}
            </span>
        @endif
    </div>
</div>
