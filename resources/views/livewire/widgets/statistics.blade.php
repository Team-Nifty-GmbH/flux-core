<x-card padding="px-16 py-8 md:px-16" x-data="{revenue: $wire.entangle('revenue')}">
    <x-slot:title>
        <p class="truncate text-xl text-gray-500">{{__('Statistics')}}</p>
    </x-slot:title>
    <div class="grid grid-cols-4 gap-10">
        <div class="flex gap-5 items-start">
            <x-button.circle xl class="cursor-default" icon="shopping-cart" purple></x-button.circle>
            <div>
                <p class="text-2xl font-semibold text-gray-900">{{$salesCount}}</p>
                <p class="truncate text-lgfont-medium text-gray-500">{{__('Sales')}}</p>
            </div>
        </div>

        <div class="flex gap-5 items-start">
            <x-button.circle xl class="cursor-default" icon="user-group" primary></x-button.circle>
            <div>
                <p class="text-2xl font-semibold text-gray-900">{{$activeCustomersCount}}</p>
                <p class="truncate text-lgfont-medium text-gray-500">{{__('Customers')}}</p>
            </div>
        </div>

        <div class="flex gap-5 items-start">
            <x-button.circle xl class="cursor-default" icon="archive" red></x-button.circle>
            <div>
                <p class="text-2xl font-semibold text-gray-900">{{$activeProductsCount}}</p>
                <p class="truncate text-lgfont-medium text-gray-500">{{__('Products')}}</p>
            </div>
        </div>

        <div class="flex gap-5 items-start">
            <x-button.circle xl class="cursor-default" icon="currency-dollar" positive></x-button.circle>
            <div>
                <p class="text-2xl font-semibold text-gray-900" x-text="formatters.money(revenue, @js($currency))"></p>
                <p class="truncate text-lgfont-medium text-gray-500">{{__('Revenue')}}</p>
            </div>
        </div>
    </div>
</x-card>
