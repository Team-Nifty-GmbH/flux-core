<div class="max-w-full h-full flex flex-col" x-data="{total: $wire.entangle('sum', true)}">
    <div class="flex-1 flex p-2.5 border-b mb-4 text-ellipsis overflow-hidden flex-col items-start overflow-auto">
        <x-button.circle xl class="cursor-default" icon="credit-card" orange></x-button.circle>
        <h2 class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400">{{  __('Total profits') }}</h2>
        <div class="flex flex-col items-start pt-3">
            <p class="text-lg font-normal text-gray-500" x-text="formatters.money(total, @js($currency))"></p>
        </div>
    </div>
</div>
