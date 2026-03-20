<div class="flex max-h-full flex-col gap-4 p-4">
    <div>
        <div class="flex items-center justify-between">
            <h2 class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400">
                {{ $this->getLabel() }}
            </h2>
            @if ($count > 0)
                <x-badge color="red" :text="(string) $count" />
            @endif
        </div>
        <hr class="mt-2" />
    </div>
    <div class="flex-1 overflow-auto">
        @forelse ($tickets as $ticket)
            <div class="flex items-start gap-3 py-3 {{ ! $loop->last ? 'border-b border-gray-100 dark:border-gray-700/50' : '' }}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $ticket->title }}
                        </span>
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        {!! $ticket->state->badge() !!}
                        <span class="truncate">{{ data_get($ticket, 'authenticatable.name') }}</span>
                        @if ($ticket->created_at)
                            <span>&middot;</span>
                            <span>{{ $ticket->created_at->locale(app()->getLocale())->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-none items-center">
                    <x-button
                        color="secondary"
                        light
                        icon="eye"
                        :title="__('View')"
                        wire:navigate
                        :href="route('tickets.id', $ticket->getKey())"
                    />
                </div>
            </div>
        @empty
            <div class="p-4 text-center text-sm text-gray-400">
                {{ __('No escalated tickets') }}
            </div>
        @endforelse
        @if ($hasMore)
            <div class="flex justify-center pt-2">
                <x-button
                    color="secondary"
                    light
                    loading="loadMore"
                    :text="__('Load more')"
                    wire:click="loadMore()"
                />
            </div>
        @endif
    </div>
</div>
