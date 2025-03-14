<div class="flex max-h-full flex-col !px-0 !py-0">
    <div class="border-b border-gray-200 pb-2 pl-2 pt-2">
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ __('Purchase Invoice Approval') }}
        </h2>
    </div>
    <div
        class="flex-1 overflow-auto"
        x-data="{ formatter: @js(resolve_static(\FluxErp\Models\Ticket::class, 'typeScriptAttributes')) }"
    >
        @forelse ($invoices as $invoice)
            <x-flux::list-item :item="$invoice">
                <x-slot:avatar>
                    <x-avatar :image="$invoice->contact->getAvatarUrl()" />
                </x-slot>
                <x-slot:value>
                    <span
                        x-html="
                            window.formatters.coloredMoney(
                                {{ $invoice->total_gross_price }},
                                '{{ $invoice->currency->symbol }}',
                            )
                        "
                    ></span>
                </x-slot>
                <x-slot:sub-value>
                    <div class="flex gap-1.5">
                        <div>
                            {{ $invoice->contact->invoiceAddress->name }}
                        </div>
                        <div>
                            {{ $invoice->invoice_number }}
                        </div>
                        <div>
                            {{ $invoice->invoice_date->locale(app()->getLocale())->isoFormat('L') }}
                        </div>
                    </div>
                </x-slot>
                <x-slot:actions>
                    <x-button
                        color="secondary"
                        light
                        icon="eye"
                        wire:navigate
                        :href="route('orders.id', $invoice->id)"
                    >
                        <div class="hidden sm:block">{{ __('View') }}</div>
                    </x-button>
                </x-slot>
            </x-flux::list-item>
        @empty
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                {{ __('No invoices found') }}
            </div>
        @endforelse
    </div>
</div>
