<x-card class="w-full">
    <x-table :headers="$this->bookingHeaders" :rows="$this->bookings" striped>
        @interact('column_origin', $row)
            <x-badge
                :text="$row['origin']"
                :color="$row['origin'] === __('Repayment') ? 'indigo' : 'gray'"
                sm
            />
        @endinteract
    </x-table>
</x-card>
