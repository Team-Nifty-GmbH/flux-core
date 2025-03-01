<div x-data="{
    address: $wire.entangle('address', true),
    formatter: @js(resolve_static(\FluxErp\Models\Address::class, 'typeScriptAttributes')),
    hrefFromContactOption(type, value) {
        switch (type) {
            case 'phone':
                return 'tel:' + value;
            case 'email':
                return 'mailto:' + value;
            case 'website':
                return value;
            default:
                return '#';
        }
    }
}">
    @if(! $withoutHeader)
        <x-slot:header>
            <div class="flex items-center justify-between border-b px-4 py-2.5 dark:border-0">
                <div class="flex">
                    <x-avatar squared :image="data_get($address, 'avatar')" />
                    <div class="pl-2">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-50">
                            {{ data_get($address, 'label') }}
                        </div>
                        <x-label class="opacity-60" :label="data_get($address, 'description')" />
                    </div>
                </div>
                <div class="pl-2">
                    <x-button color="secondary" light outline icon="eye" href="{{ route('contacts.id?', data_get($address, 'contact_id')) }}">
                    </x-button>
                </div>
            </div>
        </x-slot:header>
    @endif
    <div class="pb-2 font-semibold uppercase">{{ __('Purchase activity') }}</div>
    <div class="grid grid-cols-2 gap-2">
        <x-label :label="__('Total net')" />
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
            <span x-html="formatters.coloredMoney(address.total_net)"></span>
        </div>
        <x-label :label="__('Invoices')" />
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
            <span x-html="address.total_invoices"></span>
        </div>
        <x-label :label="__('Balance')" />
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
            <span x-html="formatters.coloredMoney(address.balance)"></span>
        </div>
        <x-label :label="__('Revenue this year')" />
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
            <span x-html="formatters.coloredMoney(address.revenue_this_year)"></span>
        </div>
        <x-label :label="__('Revenue last year')" />
        <div class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px">
            <span x-html="formatters.coloredMoney(address.revenue_last_year)"></span>
        </div>
        <hr class="col-span-2"/>
        <div class="col-span-2 flex flex-col gap-2">
            <div class="pb-2 font-semibold uppercase">{{ __('Orders') }}</div>
            <template x-for="order in address.orders" :key="order.id">
                <div class="grid grid-cols-2 gap-2">
                    <x-label>
                        <x-slot:word>
                            <span x-text="order.name"></span>
                        </x-slot:word>
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
        <x-label :label="__('Phone')" />
        <a x-bind:href="'tel:' + address.phone" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="address.phone">
        </a>
        <x-label :label="__('Phone Mobile')" />
        <a x-bind:href="'tel:' + address.phone_mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="address.phone_mobile">
        </a>
        <x-label :label="__('E-mail')" />
        <a x-bind:href="'mailto:' + address.email_primary" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="address.email_primary">
        </a>
        <x-label :label="__('Website')" />
        <a x-bind:href="address.url" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="address.url">
        </a>
    </div>
    <div class="pt-2 flex flex-col gap-2">
        <template x-for="contactOption in address.contact_options" :key="contactOption.id">
            <div class="grid grid-cols-2 gap-2">
                <x-label>
                    <x-slot:word>
                        <span x-text="contactOption.label"></span>
                    </x-slot:word>
                </x-label>
                <a x-bind:href="hrefFromContactOption(contactOption.type, contactOption.value)" class="block text-sm font-medium text-gray-700 dark:text-gray-50 sm:mt-px" x-text="contactOption.value">
                </a>
            </div>
        </template>
    </div>
</div>
