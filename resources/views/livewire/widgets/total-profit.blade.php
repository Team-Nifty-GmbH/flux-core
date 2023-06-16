<x-card class="max-w-full" x-data="{total: $wire.entangle('sum')}">
    <div class="flex text-ellipsis overflow-hidden flex-col items-start overflow-auto">
        <x-button.circle xl class="cursor-default" icon="credit-card" orange></x-button.circle>
        <p class="truncate text-xl font-semibold pt-3">{{__('Total profits')}}</p>
        <div class="flex flex-col items-start pt-3">
            <p class="truncate text-md font-medium pt-1">{{__($selectedTimeFrame)}}</p>
            <p class="text-lg font-normal text-gray-500" x-text="formatters.money(total, @js($currency))"></p>
        </div>
    </div>
    <x-slot:footer>
        <x-select
            label="Select Time Frame"
            placeholder="Select a time frame"
            :options="$timeFrames"
            wire:model="timeFrame"
            :clearable="false"
        />
    </x-slot:footer>
</x-card>
