<div
    id="task-details"
    x-data="{
        task: $wire.entangle('task'),
        edit: false,
    }"
>
    <x-modal
        id="replicate-task-modal"
        :title="__('Replicate Task')"
        size="5xl"
        persistent
        x-on:close="$wire.taskId = $wire.task.id"
    >
        @section('replicate-task-modal')
        <div class="flex flex-col gap-2">
            <x-select.styled
                :label="__('Task')"
                wire:model="taskId"
                required
                x-on:select="$wire.updateReplica($event.detail.select.id)"
                select="label:label|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Task::class),
                    'method' => 'POST',
                ]"
            />
            <x-input wire:model="replica.name" :label="__('Name')" />
            <x-select.styled
                :label="__('Project')"
                wire:model="replica.project_id"
                select="label:label|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Project::class),
                    'method' => 'POST',
                ]"
            />
            <x-select.styled
                :label="__('Responsible User')"
                autocomplete="off"
                wire:model="replica.responsible_user_id"
                x-bind:readonly="!edit"
                select="label:label|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\User::class),
                    'method' => 'POST',
                    'params' => [
                        'with' => 'media',
                    ],
                ]"
            />
            <div class="flex justify-between gap-x-4">
                <div class="flex flex-1 flex-col gap-2">
                    <div class="flex flex-col gap-2">
                        <x-date
                            :label="__('Start Date')"
                            wire:model="replica.start_date"
                        />
                        <x-input
                            type="time"
                            :label="__('Start Time')"
                            wire:model="replica.start_time"
                        />
                    </div>
                    <div
                        class="flex flex-col gap-2"
                        x-cloak
                        x-show="$wire.replica.start_date"
                    >
                        <x-toggle
                            :label="__('Start Reminder')"
                            wire:model="replica.has_start_reminder"
                        />
                        <div
                            x-cloak
                            x-show="$wire.replica.has_start_reminder"
                        >
                            <x-number
                                :label="__('Remind Minutes Before')"
                                wire:model="replica.start_reminder_minutes_before"
                                min="0"
                                :hint="__('Leave empty for reminder at start time')"
                            />
                        </div>
                    </div>
                </div>
                <div class="flex flex-1 flex-col gap-2">
                    <div class="flex flex-col gap-2">
                        <x-date
                            :label="__('Due Date')"
                            wire:model="replica.due_date"
                        />
                        <x-input
                            type="time"
                            :label="__('Due Time')"
                            wire:model="replica.due_time"
                        />
                    </div>
                    <div
                        class="flex flex-col gap-2"
                        x-cloak
                        x-show="$wire.replica.due_date"
                    >
                        <x-toggle
                            :label="__('Due Reminder')"
                            wire:model="replica.has_due_reminder"
                        />
                        <div x-cloak x-show="$wire.replica.has_due_reminder">
                            <x-number
                                :label="__('Remind Minutes Before')"
                                wire:model="replica.due_reminder_minutes_before"
                                min="0"
                                :hint="__('Leave empty for reminder at due time')"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <x-number
                :label="__('Priority')"
                wire:model="replica.priority"
                min="0"
            />
            <x-flux::editor
                x-model="edit"
                wire:model="replica.description"
                :label="__('Description')"
            />
            <x-select.styled
                :label="__('Categories')"
                wire:model="replica.categories"
                x-bind:readonly="!edit"
                multiple
                select="label:label|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Category::class),
                    'method' => 'POST',
                    'params' => [
                        'where' => [
                            [
                                'model_type',
                                '=',
                                morph_alias(\FluxErp\Models\Task::class),
                            ],
                        ],
                    ],
                ]"
            />
            <x-select.styled
                :label="__('Assigned')"
                autocomplete="off"
                multiple
                wire:model="replica.users"
                x-bind:readonly="!edit"
                select="label:label|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\User::class),
                    'method' => 'POST',
                    'params' => [
                        'with' => 'media',
                    ],
                ]"
            />
            <x-select.styled
                multiple
                wire:model.number="replica.tags"
                select="label:label|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Tag::class),
                    'method' => 'POST',
                    'params' => [
                        'option-value' => 'id',
                        'where' => [
                            [
                                'type',
                                '=',
                                morph_alias(\FluxErp\Models\Task::class),
                            ],
                        ],
                    ],
                ]"
            >
                <x-slot:label>
                    <div class="flex items-center gap-2">
                        <x-label :label="__('Tags')" />
                        @canAction(\FluxErp\Actions\Tag\CreateTag::class)
                            <x-button.circle
                                sm
                                icon="plus"
                                color="emerald"
                                wire:click="addTag($promptValue())"
                                wire:flux-confirm.prompt="{{ __('New Tag') }}||{{ __('Cancel') }}|{{ __('Save') }}"
                            />
                        @endcanAction
                    </div>
                </x-slot>
            </x-select.styled>
            <x-number
                :label="__('Budget')"
                wire:model="replica.budget"
                step="0.01"
            />
            <x-input
                :label="__('Time Budget')"
                wire:model.blur="replica.time_budget"
                :corner-hint="__('Hours:Minutes')"
                placeholder="02:30"
            />
        </div>
        @show
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                x-on:click="$modalClose('replicate-task-modal')"
                :text="__('Cancel')"
            />
            <x-button
                color="primary"
                wire:click="replicate()"
                primary
                :text="__('Save')"
            />
        </x-slot>
    </x-modal>
    <div
        class="mx-auto md:flex md:items-center md:justify-between md:space-x-5"
    >
        <div class="flex items-center space-x-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                    <div class="flex">
                        <div class="pl-2">
                            <span x-text="task.name"></span>
                        </div>
                    </div>
                </h1>
            </div>
        </div>
        <div
            class="mt-6 flex flex-col-reverse justify-stretch space-y-4 space-y-reverse sm:flex-row-reverse sm:justify-end sm:space-x-3 sm:space-y-0 sm:space-x-reverse md:mt-0 md:flex-row md:space-x-3"
        >
            @canAction(\FluxErp\Actions\WorkTime\CreateWorkTime::class)
                <x-button
                    color="secondary"
                    light
                    icon="clock"
                    x-on:click="
                        $dispatch(
                            'start-time-tracking',
                            {
                                trackable_type: '{{ morph_alias(\FluxErp\Models\Task::class) }}',
                                trackable_id: {{ $task->id }},
                                name: {{ json_encode($task->name) }},
                                description: {{ json_encode(strip_tags($task->description ?? '')) }}
                            }
                        )"
                >
                    <div class="hidden sm:block">
                        {{ __('Track Time') }}
                    </div>
                </x-button>
            @endcanAction

            @canAction(\FluxErp\Actions\Task\DeleteTask::class)
                <x-button
                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Task')]) }}"
                    color="red"
                    :text="__('Delete')"
                    wire:click="delete()"
                />
            @endcanAction

            @canAction(\FluxErp\Actions\Task\ReplicateTask::class)
                <x-button
                    color="indigo"
                    :text="__('Replicate')"
                    wire:click="showReplicate()"
                />
            @endcanAction

            @canAction(\FluxErp\Actions\Task\UpdateTask::class)
                <x-button
                    color="indigo"
                    x-show="!edit"
                    class="w-full"
                    x-on:click="edit = true"
                    :text="__('Edit')"
                />
                <x-button
                    x-cloak
                    color="indigo"
                    loading
                    x-show="edit"
                    class="w-full"
                    x-on:click="$wire.save().then((success) => {
                        edit = false;
                    });"
                    :text="__('Save')"
                />
                <x-button
                    x-cloak
                    color="indigo"
                    loading
                    x-show="edit"
                    class="w-full"
                    x-on:click="edit = false; $wire.resetForm();"
                    :text="__('Cancel')"
                />
            @endcanAction
        </div>
    </div>
    <x-flux::tabs
        wire:model.live="taskTab"
        :$tabs
        wire:loading="taskTab"
        card
    />
</div>
