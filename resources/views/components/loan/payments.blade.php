<x-card class="w-full">
    <x-table :headers="$this->paymentHeaders" :rows="$this->payments" striped>
        @interact('column_purpose', $row)
            <a
                class="text-indigo-600 underline hover:text-indigo-800 dark:text-indigo-400"
                href="{{ route('accounting.transactions') }}"
                wire:navigate
            >
                {{ $row['purpose'] }}
            </a>
        @endinteract

        @interact('column_is_accepted', $row)
            <x-badge
                :text="$row['is_accepted'] ? __('Accepted') : __('Suggestion')"
                :color="$row['is_accepted'] ? 'green' : 'amber'"
                sm
            />
        @endinteract
    </x-table>
</x-card>
