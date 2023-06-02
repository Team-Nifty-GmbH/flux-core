<x-card class="max-w-full" x-data="{total: $wire.entangle('sum')}">
    <div class="flex text-ellipsis overflow-hidden flex-col items-start overflow-auto">
        <p class="truncate text-xl font-medium text-gray-500">{{__('Outstanding invoinces')}}</p>
        <div class="flex flex-col items-start pt-3">
            <p class="text-lg font-semibold" x-text="formatters.money(total, @js($currency))"></p>
        </div>
    </div>
    <x-slot:footer>
        <div class="flex justify-end">
            <x-button primary label="View all" wire:click="viewOrders" />
        </div>
    </x-slot:footer>
</x-card>

