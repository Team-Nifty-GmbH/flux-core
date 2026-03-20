<div class="flex max-h-full flex-col gap-4 p-4">
    <div>
        <div class="flex items-center justify-between">
            <h2 class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400">
                {{ $this->getLabel() }}
            </h2>
            @section('options')
            <div class="flex-none">
                <x-dropdown icon="ellipsis-vertical" static>
                    @foreach ($this->options() ?? [] as $option)
                        <x-dropdown.items
                            :text="data_get($option, 'label')"
                            wire:click="{{ data_get($option, 'method') }}({{ json_encode(data_get($option, 'params', [])) }})"
                        />
                    @endforeach
                </x-dropdown>
            </div>
            @show
        </div>
        <hr class="mt-2" />
    </div>
    <div class="flex-1 overflow-auto">
        @forelse ($leads as $lead)
            <div class="flex items-start gap-3 py-3 {{ ! $loop->last ? 'border-b border-gray-100 dark:border-gray-700/50' : '' }}">
                <div class="flex-none pt-0.5">
                    <x-avatar xs :image="$lead->leadState->image" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $lead->name }}
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="truncate">{{ $lead->address?->name ?? __('Unknown') }}</span>
                        @if ($lead->end)
                            <span>&middot;</span>
                            <span class="{{ $lead->end->isPast() ? 'text-red-500' : '' }}">
                                {{ $lead->end->locale(app()->getLocale())->diffForHumans() }}
                            </span>
                        @endif
                        <span>&middot;</span>
                        <span>{{ \Illuminate\Support\Number::percentage(bcmul($lead->probability_percentage, 100)) }}</span>
                    </div>
                </div>
                <div class="flex flex-none items-center">
                    <x-button
                        color="secondary"
                        light
                        icon="eye"
                        :title="__('View')"
                        wire:navigate
                        :href="route('sales.lead.id', $lead->getKey())"
                    />
                </div>
            </div>
        @empty
            <div class="p-4 text-center text-sm text-gray-400">
                {{ __('No leads found') }}
            </div>
        @endforelse
    </div>
</div>
