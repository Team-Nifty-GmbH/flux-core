<div class="!py-0 !px-0 max-h-full flex flex-col">
    <div class="border-b pb-2 pt-2 pl-2 border-gray-200">
        <h2 class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400">{{ __($this->getLabel()) }}</h2>
    </div>
    <div class="flex-1 overflow-auto">
        @forelse($tickets as $ticket)
            <x-flux::list-item :item="$ticket" value="title">
                <x-slot:avatar>
                    {!! $ticket->state->badge() !!}
                </x-slot:avatar>
                <x-slot:sub-value>
                    <div>
                        <div>{{ data_get($ticket, 'authenticatable.name') }}</div>
                        <div>{{ \Illuminate\Support\Str::limit($ticket?->description) }}</div>
                        @if($ticket->created_at)
                            <x-badge
                                :color="($diff = $ticket->created_at->diffInDays(now(), false)) > 3
                                    ? 'negative'
                                    : ($diff > 2 ? 'warning' : 'positive')
                                "
                                :label="__('Created At') . ' ' . $ticket->created_at->locale(app()->getLocale())->isoFormat('L')"
                            />
                        @endif
                    </div>
                </x-slot:sub-value>
                <x-slot:actions>
                    <x-button
                        icon="clock"
                        x-on:click="
                            $dispatch(
                                'start-time-tracking',
                                {
                                    trackable_type: 'FluxErp\\\Models\\\Ticket',
                                    trackable_id: {{ $ticket->id }},
                                    name: '{{ $ticket->title }}',
                                    description: {{ json_encode($ticket->description) }}
                                }
                            )"
                    >
                        <div class="hidden sm:block">{{ __('Track Time') }}</div>
                    </x-button>
                    <x-button icon="eye" wire:navigate :href="route('tickets.id', $ticket->id)">
                        <div class="hidden sm:block">{{ __('View') }}</div>
                    </x-button>
                </x-slot:actions>
            </x-flux::list-item>
        @empty
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                {{ __('No tickets found') }}
            </div>
        @endforelse
    </div>
</div>
