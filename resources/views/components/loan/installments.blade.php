<x-card class="w-full">
    <div
        class="dark:divide-secondary-700 dark:border-secondary-700 mb-6 grid grid-cols-1 divide-y divide-gray-200 rounded-lg border border-gray-200 sm:grid-cols-3 sm:divide-x sm:divide-y-0"
    >
        <div class="px-4 py-3">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('Principal') }}
            </div>
            <div
                class="mt-1 text-lg font-semibold text-gray-900 tabular-nums dark:text-gray-50"
            >
                {{ $this->totals['principal_amount'] }}
            </div>
            <div
                class="mt-0.5 text-xs text-gray-500 tabular-nums dark:text-gray-400"
            >
                {{ __('Paid') }} {{ $this->totals['paid_principal_amount'] }}
                <span class="opacity-60">
                    ({{ $this->totals['paid_principal_share'] }})
                </span>
            </div>
        </div>
        <div class="px-4 py-3">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('Interest') }}
            </div>
            <div
                class="mt-1 text-lg font-semibold text-gray-900 tabular-nums dark:text-gray-50"
            >
                {{ $this->totals['interest_amount'] }}
            </div>
            <div
                class="mt-0.5 text-xs text-gray-500 tabular-nums dark:text-gray-400"
            >
                {{ __('Paid') }} {{ $this->totals['paid_interest_amount'] }}
                <span class="opacity-60">
                    ({{ $this->totals['paid_interest_share'] }})
                </span>
            </div>
        </div>
        <div class="bg-gray-50/70 px-4 py-3 dark:bg-white/5">
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ __('Total') }}
            </div>
            <div
                class="mt-1 text-lg font-semibold text-gray-900 tabular-nums dark:text-gray-50"
            >
                {{ $this->totals['total'] }}
            </div>
            <div
                class="mt-0.5 text-xs text-gray-500 tabular-nums dark:text-gray-400"
            >
                {{ __('Paid') }} {{ $this->totals['paid_total'] }}
                <span class="opacity-60">
                    ({{ $this->totals['paid_total_share'] }})
                </span>
            </div>
        </div>
    </div>

    <x-table
        :headers="$this->scheduleHeaders"
        :rows="$this->installments"
        striped
    >
        @interact('column_status', $row)
            <x-badge
                :text="$row['status']"
                :color="match ($row['status']) {
                    __('Settled') => 'green',
                    __('Overdue') => 'red',
                    __('Partially Paid') => 'amber',
                    default => 'gray',
                }"
                sm
            />
        @endinteract
    </x-table>
</x-card>
