<div class="flex max-h-full flex-col px-0! py-0!">
    <div class="border-b border-gray-200 pt-2 pb-2 pl-2">
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ __($this->getLabel()) }}
        </h2>
    </div>
    <div class="flex-1 overflow-auto">
        @forelse ($tickets as $ticket)
            <x-flux::list-item :item="$ticket" value="title">
                <x-slot:avatar>
                    {!! $ticket->state->badge() !!}
                </x-slot>
                <x-slot:sub-value>
                    <div>
                        <div>
                            {{ data_get($ticket, 'authenticatable.name') }}
                        </div>
                        <div>
                            {{ \Illuminate\Support\Str::limit(strip_tags($ticket?->description, '')) }}
                        </div>
                        @if ($ticket->created_at)
                            <x-badge
                                :color="($diff = $ticket->created_at->diffInDays(now(), false)) > 3
                                    ? 'red'
                                    : ($diff > 2 ? 'amber' : 'emerald')
                                "
                                :text="__('Created At') . ' ' . $ticket->created_at->locale(app()->getLocale())->isoFormat('L')"
                            />
                        @endif
                    </div>
                </x-slot>
                <x-slot:actions>
                    <x-button
                        color="secondary"
                        light
                        icon="clock"
                        x-on:click="
                            $dispatch(
                                'start-time-tracking',
                                {
                                    trackable_type: 'FluxErp\\\Models\\\Ticket',
                                    trackable_id: {{ $ticket->id }},
                                    name: {{ json_encode($ticket->title) }},
                                    description: {{ json_encode(strip_tags($ticket->description ?? '')) }}
                                }
                            )"
                    >
                        <div class="hidden sm:block">
                            {{ __('Track Time') }}
                        </div>
                    </x-button>
                    <x-button
                        color="secondary"
                        light
                        icon="eye"
                        wire:navigate
                        :href="route('tickets.id', $ticket->id)"
                    >
                        <div class="hidden sm:block">{{ __('View') }}</div>
                    </x-button>
                </x-slot>
            </x-flux::list-item>
        @empty
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                {{ __('No tickets found') }}
            </div>
        @endforelse
    </div>
</div>
