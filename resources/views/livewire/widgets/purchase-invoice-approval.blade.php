<<<<<<< HEAD
<div class="flex max-h-full flex-col px-0! py-0!">
    <div class="border-b border-gray-200 pb-2 pl-2 pt-2">
=======
<div class="flex max-h-full flex-col gap-4 p-4">
    <div>
>>>>>>> feature/auto-inject-frontend-assets
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ __('Purchase Invoice Approval') }}
        </h2>
        <hr class="mt-2" />
    </div>
    <div class="flex-1 overflow-auto">
        @forelse ($invoices as $invoice)
            <div
                class="{{ ! $loop->last ? 'border-b border-gray-100 dark:border-gray-700/50' : '' }} flex items-start gap-3 py-3"
            >
                <div class="flex-none pt-0.5">
                    <x-avatar xs :image="$invoice->contact->getAvatarUrl()" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span
                            class="truncate text-sm font-medium text-gray-900 dark:text-gray-100"
                        >
                            {{ $invoice->contact->invoiceAddress->name }}
                        </span>
                        <span
                            class="flex-none text-sm font-bold"
                            x-html="
                                window.formatters.coloredMoney(
                                    {{ $invoice->total_gross_price }},
                                    '{{ $invoice->currency->symbol }}',
                                )
                            "
                        ></span>
                    </div>
                    <div
                        class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400"
                    >
                        <span>{{ $invoice->invoice_number }}</span>
                        <span>&middot;</span>
                        <span>
                            {{ $invoice->invoice_date->locale(app()->getLocale())->isoFormat('L') }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-none items-center">
                    <x-button
                        color="secondary"
                        light
                        icon="eye"
                        :title="__('View')"
                        wire:navigate
                        :href="route('orders.id', $invoice->getKey())"
                    />
                </div>
            </div>
        @empty
            <div class="p-4 text-center text-sm text-gray-400">
                {{ __('No invoices found') }}
            </div>
        @endforelse
    </div>
</div>
