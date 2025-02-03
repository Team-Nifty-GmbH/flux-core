<div class="space-y-8 divide-y divide-gray-200">
    <div class="space-y-2.5"
         x-data="{
            formatter: @js(resolve_static(\FluxErp\Models\Task::class, 'typeScriptAttributes')),
         }"
    >
        @section('task-content')
            <x-input x-bind:readonly="!edit" wire:model="task.name" label="{{ __('Name') }}" />
            @section('task-content.selects')
                @section('task-content.selects.project')
                    <div x-show="task.id" x-bind:class="!edit && 'pointer-events-none'">
                        <x-select
                            :label="__('Project')"
                            option-value="id"
                            option-label="label"
                            option-description="description"
                            wire:model="task.project_id"
                            x-bind:readonly="!edit"
                            :async-data="[
                                'api' => route('search', \FluxErp\Models\Project::class),
                                'method' => 'POST',
                            ]"
                        />
                    </div>
                @show
                @section('task-content.selects.responsible-users')
                    <div x-bind:class="!edit && 'pointer-events-none'">
                        <x-select
                            :label="__('Responsible User')"
                            option-value="id"
                            option-label="label"
                            autocomplete="off"
                            wire:model="task.responsible_user_id"
                            x-bind:readonly="!edit"
                            :template="[
                                'name'   => 'user-option',
                            ]"
                            :async-data="[
                                'api' => route('search', \FluxErp\Models\User::class),
                                'method' => 'POST',
                                'params' => [
                                    'with' => 'media',
                                ],
                            ]"
                        />
                    </div>
                @show
            @show
            <div class="flex justify-between gap-x-4" x-bind:class="!edit && 'pointer-events-none'">
                @section('task-content.dates')
                    <x-datetime-picker
                        x-bind:readonly="!edit"
                        :without-time="true"
                        wire:model="task.start_date"
                        label="{{ __('Start Date') }}"
                    />
                    <x-datetime-picker
                        x-bind:readonly="!edit"
                        :without-time="true"
                        wire:model="task.due_date"
                        label="{{ __('Due Date') }}"
                    />
                @show
            </div>
            @section('task-content.multi-selects')
                <x-state
                    x-bind:class="!edit && 'pointer-events-none'"
                    class="w-full"
                    align="left"
                    :label="__('Task state')"
                    wire:model="task.state"
                    formatters="formatter.state"
                    available="availableStates"
                />
            @show
            <x-inputs.number x-bind:readonly="!edit" :label="__('Priority')" wire:model="task.priority" min="0" />
            <x-textarea x-bind:readonly="!edit" wire:model="task.description" label="{{ __('Description') }}" />
            <div x-bind:class="!edit && 'pointer-events-none'">
                <x-select
                    :label="__('Categories')"
                    wire:model="task.categories"
                    x-bind:readonly="!edit"
                    multiselect
                    option-value="id"
                    option-label="label"
                    :async-data="[
                        'api' => route('search', \FluxErp\Models\Category::class),
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
                <x-select
                    :label="__('Assigned')"
                    option-value="id"
                    option-label="label"
                    autocomplete="off"
                    multiselect
                    wire:model="task.users"
                    x-bind:readonly="!edit"
                    :template="[
                        'name'   => 'user-option',
                    ]"
                    :async-data="[
                        'api' => route('search', \FluxErp\Models\User::class),
                        'method' => 'POST',
                        'params' => [
                            'with' => 'media',
                        ],
                    ]"
                />
            </div>
            <div class="col-span-2" x-bind:class="!edit && 'pointer-events-none'">
                <x-select
                    :label="__('Tags')"
                    multiselect
                    x-bind:disabled="! edit"
                    wire:model.number="task.tags"
                    option-value="id"
                    option-label="label"
                    :async-data="[
                        'api' => route('search', \FluxErp\Models\Tag::class),
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
                    <x-slot:beforeOptions>
                        <div class="px-1">
                            <x-button positive full :label="__('Add')" wire:click="addTag($promptValue())" wire:flux-confirm.prompt="{{ __('New Tag') }}||{{ __('Cancel') }}|{{ __('Save') }}" />
                        </div>
                    </x-slot:beforeOptions>
                </x-select>
            </div>
            <x-inputs.number x-bind:readonly="!edit" :label="__('Budget')" wire:model="task.budget" step="0.01" />
            <x-input x-bind:readonly="!edit"
                     :label="__('Time Budget')"
                     wire:model.blur="task.time_budget"
                     :corner-hint="__('Hours:Minutes')"
                     placeholder="02:30"
            />
        @show
    </div>
    @section('task-additional-columns')
        <div class="space-y-2.5">
            <h3 class="font-medium whitespace-normal text-md text-secondary-700 dark:text-secondary-400 mt-4">
                {{ __('Additional Columns') }}
            </h3>
            <x-flux::additional-columns :model="\FluxErp\Models\Task::class" :id="$this->task->id" wire="task.additionalColumns"/>
        </div>
    @show
</div>
