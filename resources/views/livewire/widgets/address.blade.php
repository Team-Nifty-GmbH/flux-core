<div x-data="{
    address: $wire.entangle('address', true),
    formatter: @js(resolve_static(\FluxErp\Models\Address::class, 'typeScriptAttributes')),
}">
    @if(! $withoutHeader)
        <x-slot:header>
            <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                <div class="flex">
                    <x-avatar squared :src="$address['avatar']" />
                    <div class="pl-2">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-50">
                            {{ $address['label'] }}
                        </div>
                        <x-label class="opacity-60">
                            {{ $address['description'] }}
                        </x-label>
                    </div>
                </div>
                <div class="pl-2">
                    <x-button outline icon="eye" href="{{ route('contacts.id?', $address['contact_id']) }}">
                    </x-button>
                </div>
            </div>
        </x-slot:header>
    @endif
    <div class="pb-2 font-semibold uppercase">{{ __('Purchase activity') }}</div>
    <div class="grid grid-cols-2 gap-2">
        <x-label>
            {{ __('Total net') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
            <span x-html="formatters.coloredMoney(address.total_net)"></span>
        </div>
        <x-label>
            {{ __('Invoices') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
            <span x-html="address.total_invoices"></span>
        </div>
        <x-label>
            {{ __('Balance') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
            <span x-html="formatters.coloredMoney(address.balance)"></span>
        </div>
        <x-label>
            {{ __('Revenue this year') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
            <span x-html="formatters.coloredMoney(address.revenue_this_year)"></span>
        </div>
        <x-label>
            {{ __('Revenue last year') }}
        </x-label>
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
            <span x-html="formatters.coloredMoney(address.revenue_last_year)"></span>
        </div>
        <hr class="col-span-2"/>
        <div class="col-span-2 flex flex-col gap-2">
            <div class="pb-2 font-semibold uppercase">{{ __('Orders') }}</div>
            <template x-for="order in address.orders" :key="order.id">
                <div class="grid grid-cols-2 gap-2">
                    <x-label>
                        <span x-text="order.name"></span>
                    </x-label>
                    <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
                        <span x-html="order.orders_count"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>
    <hr class="col-span-2"/>
    <div class="pt-8 pb-2 font-semibold uppercase">{{ __('Contact options') }}</div>
    <div class="grid grid-cols-2 gap-2">
        <x-label>
            {{ __('Phone') }}
        </x-label>
        <a x-bind:href="'tel:' + address.phone" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="address.phone">
        </a>
        <x-label>
            {{ __('E-mail') }}
        </x-label>
        <a x-bind:href="'mailto:' + address.email" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="address.email">
        </a>
        <x-label>
            {{ __('Website') }}
        </x-label>
        <a x-bind:href="address.website" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="address.website">
        </a>
    </div>
</div>
