<div class="flex max-h-full flex-col gap-4 p-4">
    <div>
        <h2 class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400">
            {{ $this->getLabel() }}
        </h2>
        <hr class="mt-2" />
    </div>
    <div class="flex-1 overflow-auto">
        @forelse ($tasks as $task)
            <div class="flex items-start gap-3 py-3 {{ ! $loop->last ? 'border-b border-gray-100 dark:border-gray-700/50' : '' }}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $task->name }}
                        </span>
                        <x-badge
                            :color="match (true) {
                                $task->priority === 0 => 'gray',
                                $task->priority < 5 => 'indigo',
                                $task->priority < 8 => 'amber',
                                default => 'red',
                            }"
                            :text="'P' . $task->priority"
                        />
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        {!! $task->state->badge() !!}
                        @if ($task->project?->name)
                            <span class="truncate">{{ $task->project->name }}</span>
                        @endif
                        @if ($task->due_date)
                            <span>&middot;</span>
                            <span class="{{ $task->due_date->isPast() ? 'text-red-500' : '' }}">
                                {{ $task->due_date->locale(app()->getLocale())->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-none items-center gap-1">
                    <x-button
                        color="secondary"
                        light
                        icon="clock"
                        :title="__('Track Time')"
                        x-on:click="
                            $dispatch(
                                'start-time-tracking',
                                {
                                    trackable_type: '{{ morph_alias(\FluxErp\Models\Task::class) }}',
                                    trackable_id: {{ $task->getKey() }},
                                    name: {{ json_encode($task->name) }},
                                    description: {{ json_encode(strip_tags($task->description ?? '')) }}
                                }
                            )"
                    />
                    <x-button
                        color="secondary"
                        light
                        icon="eye"
                        :title="__('View')"
                        wire:navigate
                        :href="route('tasks.id', $task->getKey())"
                    />
                </div>
            </div>
        @empty
            <div class="p-4 text-center text-sm text-gray-400">
                {{ __('No tasks found') }}
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
