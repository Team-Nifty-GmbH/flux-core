<div class="!py-0 !px-0 max-h-full flex flex-col">
    <div class="border-b pb-2 pt-2 pl-2 border-gray-200">
        <h2 class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400">{{ __('Purchase Invoice Approval') }}</h2>
    </div>
    <div class="flex-1 overflow-auto" x-data="{formatter: @js(resolve_static(\FluxErp\Models\Ticket::class, 'typeScriptAttributes'))}">
        @forelse($invoices as $invoice)
            <x-flux::list-item :item="$invoice">
                <x-slot:avatar>
                    <x-avatar :image="$invoice->contact->getAvatarUrl()" />
                </x-slot:avatar>
                <x-slot:value>
                    <span x-html="window.formatters.coloredMoney({{ $invoice->total_gross_price }}, '{{ $invoice->currency->symbol }}')"></span>
                </x-slot:value>
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
                </x-slot:sub-value>
                <x-slot:actions>
                    <x-button color="secondary" light icon="eye" wire:navigate :href="route('orders.id', $invoice->id)">
                        <div class="hidden sm:block">{{ __('View') }}</div>
                    </x-button>
                </x-slot:actions>
            </x-flux::list-item>
        @empty
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                {{ __('No invoices found') }}
            </div>
        @endforelse
    </div>
</div>
