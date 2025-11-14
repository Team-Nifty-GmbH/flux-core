<div class="flex flex-col gap-4 divide-y divide-gray-200">
    <div
        class="flex flex-col gap-2"
        x-data="{
            formatter: @js(resolve_static(\FluxErp\Models\Task::class, 'typeScriptAttributes')),
        }"
    >
        @section('task-content')
        <x-input
            x-bind:readonly="!edit"
            wire:model="task.name"
            :label="__('Name')"
        />
        @section('task.model')
        <x-link
            sm
            href="#"
            icon="link"
            x-cloak
            x-show="task.modelUrl"
            x-bind:href="task.modelUrl"
            wire:navigate
        >
            <x-slot:text>
                <span x-text="task.modelLabel"></span>
            </x-slot>
        </x-link>
        @show
        @section('task-content.selects')
        @section('task-content.selects.project')
        <div
            x-show="task.id"
            x-bind:class="!edit && 'pointer-events-none'"
        >
            <x-select.styled
                wire:model="task.project_id"
                x-bind:readonly="!edit"
                select="label:label|value:id"
                unfiltered
                :request="[
                    'url' => route('search', \FluxErp\Models\Project::class),
                    'method' => 'POST',
                ]"
            >
                <x-slot:label>
                    <x-link
                        icon="link"
                        :text="__('Project')"
                        href="#"
                        class="pointer-events-auto"
                        wire:navigate
                        x-bind:href="task.project_id ? '{{ route('projects.id', ':id') }}'.replace(':id', task.project_id) : '#'"
                    />
                </x-slot>
            </x-select.styled>
        </div>
        @show
        @section('task-content.selects.responsible-users')
        <div x-bind:class="!edit && 'pointer-events-none'">
            <x-select.styled
                :label="__('Responsible User')"
                autocomplete="off"
                wire:model="task.responsible_user_id"
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
        </div>
        @show
        @show
        <div
            class="flex justify-between gap-x-4"
            x-bind:class="!edit && 'pointer-events-none'"
        >
            @section('task-content.dates')
            @section('task-content.start')
            <div class="flex flex-col gap-2">
                <div class="flex flex-row gap-x-4">
                    <x-date
                        :label="__('Start Date')"
                        wire:model="task.start_date"
                        x-bind:readonly="!edit"
                    />
                    <x-input
                        type="time"
                        :label="__('Start Time')"
                        wire:model="task.start_time"
                        x-bind:readonly="!edit"
                    />
                </div>
                <div
                    class="flex flex-col gap-2"
                    x-cloak
                    x-show="task.start_date"
                    x-bind:class="!edit && 'pointer-events-none'"
                >
                    <x-toggle
                        :label="__('Start Reminder')"
                        wire:model="task.has_start_reminder"
                        x-bind:disabled="!edit"
                    />
                    <div x-cloak x-show="$wire.task.has_start_reminder">
                        <x-number
                            :label="__('Remind Minutes Before')"
                            wire:model="task.start_reminder_minutes_before"
                            x-bind:readonly="!edit"
                            min="0"
                            :hint="__('Leave empty for reminder at start time')"
                        />
                    </div>
                </div>
            </div>
            @show

            @section('task-content.due')
            <div class="flex flex-col gap-2">
                <div class="flex flex-row gap-x-4">
                    <x-date
                        :label="__('Due Date')"
                        wire:model="task.due_date"
                        x-bind:readonly="!edit"
                    />
                    <x-input
                        type="time"
                        :label="__('Due Time')"
                        wire:model="task.due_time"
                        x-bind:readonly="!edit"
                    />
                </div>
                <div
                    class="flex flex-col gap-2"
                    x-cloak
                    x-show="task.due_date"
                    x-bind:class="!edit && 'pointer-events-none'"
                >
                    <x-toggle
                        :label="__('Due Reminder')"
                        wire:model="task.has_due_reminder"
                        x-bind:disabled="!edit"
                    />
                    <div x-cloak x-show="$wire.task.has_due_reminder">
                        <x-number
                            :label="__('Remind Minutes Before')"
                            wire:model="task.due_reminder_minutes_before"
                            x-bind:readonly="!edit"
                            min="0"
                            :hint="__('Leave empty for reminder at due time')"
                        />
                    </div>
                </div>
            </div>
            @show
            @show
        </div>
        @section('task-content.multi-selects')
        <x-flux::state
            x-bind:class="!edit && 'pointer-events-none'"
            class="w-full"
            align="bottom-start"
            :label="__('Task state')"
            wire:model="task.state"
            formatters="formatter.state"
            available="availableStates"
        />
        @show
        <x-number
            x-bind:readonly="!edit"
            :label="__('Priority')"
            wire:model="task.priority"
            min="0"
        />
        <x-flux::editor
            x-model="edit"
            wire:model="task.description"
            :label="__('Description')"
        />
        <div x-bind:class="!edit && 'pointer-events-none'">
            <x-select.styled
                :label="__('Categories')"
                wire:model="task.categories"
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
        </div>
        <div x-bind:class="!edit && 'pointer-events-none'">
            <x-select.styled
                :label="__('Assigned')"
                autocomplete="off"
                multiple
                wire:model="task.users"
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
        </div>
        <div
            class="col-span-2"
            x-bind:class="!edit && 'pointer-events-none'"
        >
            <x-select.styled
                multiple
                x-bind:disabled="! edit"
                wire:model.number="task.tags"
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
        </div>
        <x-number
            x-bind:readonly="!edit"
            :label="__('Budget')"
            wire:model="task.budget"
            step="0.01"
        />
        <x-input
            x-bind:readonly="!edit"
            :label="__('Time Budget')"
            wire:model.blur="task.time_budget"
            :corner-hint="__('Hours:Minutes')"
            placeholder="02:30"
        />
        @show
    </div>
    @section('task-additional-columns')
    <div class="flex flex-col gap-2">
        <h3
            class="text-md mt-4 whitespace-normal font-medium text-secondary-700 dark:text-secondary-400"
        >
            {{ __('Additional Columns') }}
        </h3>
        <x-flux::additional-columns
            :model="\FluxErp\Models\Task::class"
            :id="$this->task->id"
            wire="task.additionalColumns"
        />
    </div>
    @show
</div>
