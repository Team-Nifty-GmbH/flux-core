<div class="max-w-full h-full flex flex-col" x-data="{total: $wire.entangle('sum', true)}">
    <div class="flex-1 flex p-2.5 border-b mb-4 text-ellipsis overflow-hidden flex-col items-start overflow-auto">
        <x-button.circle xl class="cursor-default" icon="credit-card" orange></x-button.circle>
        <p class="truncate dark:text-white text-gray-400 text-xl font-semibold pt-3">{{  __('Total profits') }}</p>
        <div class="flex flex-col items-start pt-3">
            <p class="text-lg font-normal text-gray-500" x-text="formatters.money(total, @js($currency))"></p>
        </div>
    </div>
        <x-select
            :label="__('Select Time Frame')"
            :options="$timeFrames"
            option-value="value"
            option-label="label"
            class="pb-4 px-1.5"
            wire:model.live="timeFrame"
            :clearable="false"
        />
</div>
