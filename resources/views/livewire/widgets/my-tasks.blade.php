<div class="!py-0 !px-0 max-h-full flex flex-col">
    <div class="border-b pb-2 pt-2 pl-2 border-gray-200">
        <h2 class="truncate text-lg font-semibold text-gray-700 dark:text-gray-400">{{ __('My Tasks') }}</h2>
    </div>
    <div class="flex-1 overflow-auto">
        @forelse($tasks as $task)
            <x-flux::list-item :item="$task">
                <x-slot:avatar>
                    {!! $task->state->badge() !!}
                </x-slot:avatar>
                <x-slot:sub-value>
                    <div>
                        <div>{{ $task->project?->name }}</div>
                        @if($task->due_date)
                            <x-badge
                                :color="($diff = $task->due_date->diffInDays(now(), false)) > 0
                                    ? 'red'
                                    : ($diff === 0 ? 'amber' : 'emerald')
                                "
                                :text="__('Due At') . ' ' . $task->due_date->locale(app()->getLocale())->isoFormat('L')"
                            />
                        @endif
                        <x-badge
                            :color="match (true) {
                                $task->priority === 0 => 'gray',
                                $task->priority < 5 => 'indigo',
                                $task->priority < 8 => 'amber',
                                default => 'red',
                            }"
                            :text="__('Priority') . ': ' . $task->priority"
                        />
                    </div>
                </x-slot:sub-value>
                <x-slot:actions>
                    <x-button color="secondary" light
                        icon="clock"
                        x-on:click="
                            $dispatch(
                                'start-time-tracking',
                                {
                                    trackable_type: 'FluxErp\\\Models\\\Task',
                                    trackable_id: {{ $task->id }},
                                    name: '{{ $task->name }}',
                                    description: {{ json_encode($task->description) }}
                                }
                            )"
                        >
                        <div class="hidden sm:block">{{ __('Track Time') }}</div>
                    </x-button>
                    <x-button color="secondary" light icon="eye" wire:navigate :href="route('tasks.id', $task->id)">
                        <div class="hidden sm:block">{{ __('View') }}</div>
                    </x-button>
                </x-slot:actions>
            </x-flux::list-item>
        @empty
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                {{ __('No tasks found') }}
            </div>
        @endforelse
    </div>
</div>
