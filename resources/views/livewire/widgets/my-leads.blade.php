<div class="flex max-h-full flex-col !px-0 !py-0">
    <div class="border-b border-gray-200 pb-2 pl-2 pt-2">
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ __('My Leads') }}
        </h2>
    </div>
    <div class="flex-1 overflow-auto">
        @forelse ($leads as $lead)
            <x-flux::list-item :item="$lead">
                <x-slot:avatar>
                    <x-avatar :image="$lead->leadState->image" />
                </x-slot>
                <x-slot:sub-value>
                    <div>
                        <div>{{ $lead->address?->name ?? __('Unknown') }}</div>
                        @if ($lead->end)
                            <x-badge
                                :color="($diff = $lead->end->diffInDays(now(), false)) > 0
                                    ? 'red'
                                    : ($diff === 0 ? 'amber' : 'emerald')
                                "
                                :text="__('End') . ' ' . $lead->end->locale(app()->getLocale())->isoFormat('L')"
                            />
                        @endif

                        <x-badge
                            :color="match (true) {
                                $lead->probability_percentage === 0 => 'gray',
                                $lead->probability_percentage < 0.5 => 'red',
                                $lead->probability_percentage < 0.8 => 'amber',
                                default => 'green',
                            }"
                            :text="__('Probability') . ': ' . \Illuminate\Support\Number::percentage(bcmul($lead->probability_percentage, 100))"
                        />
                    </div>
                </x-slot>
                <x-slot:actions>
                    <x-button
                        color="secondary"
                        light
                        icon="eye"
                        wire:navigate
                        :href="route('sales.lead.id', $lead->getKey())"
                    >
                        <div class="hidden sm:block">{{ __('View') }}</div>
                    </x-button>
                </x-slot>
            </x-flux::list-item>
        @empty
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                {{ __('No leads found') }}
            </div>
        @endforelse
    </div>
</div>
