@use('Illuminate\Support\Str')

<div class="flex max-h-full flex-col !px-0 !py-0">
    <div
        class="flex items-center justify-between border-b border-gray-200 px-4 py-2"
    >
        <h2
            class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400"
        >
            {{ $this->getLabel() }}
        </h2>
        @if ($count > 0)
            <x-badge color="red" :text="(string) $count" />
        @endif
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
                            {{ Str::limit(strip_tags($ticket->description)) }}
                        </div>
                        @if ($ticket->created_at)
                            @php
                                $diff = $ticket->created_at->diffInDays(now(), false);
                                $badgeColor = $diff > 3 ? 'red' : ($diff > 2 ? 'amber' : 'emerald');
                            @endphp

                            <x-badge
                                :color="$badgeColor"
                                :text="__('Created At') . ' ' . $ticket->created_at->locale(app()->getLocale())->isoFormat('L')"
                            />
                        @endif
                    </div>
                </x-slot>
                <x-slot:actions>
                    <x-button
                        color="secondary"
                        light
                        icon="eye"
                        wire:navigate
                        :href="route('tickets.id', $ticket->getKey())"
                        :text="__('View')"
                    />
                </x-slot>
            </x-flux::list-item>
        @empty
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                {{ __('No escalated tickets') }}
            </div>
        @endforelse
    </div>
</div>
