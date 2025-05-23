<div>
    <x-modal id="create-task-modal" :title="__('Create Task')">
        <div
            class="space-y-8 divide-y divide-gray-200"
            x-data="{
                formatter: @js(resolve_static(\FluxErp\Models\Task::class, 'typeScriptAttributes')),
            }"
        >
            <div class="space-y-2.5">
                <x-input wire:model="task.name" label="{{ __('Name') }}" />
                <x-select.styled
                    :label="__('Project')"
                    wire:model="task.project_id"
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
                    wire:model="task.responsible_user_id"
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
                    <x-input
                        type="date"
                        wire:model="task.start_date"
                        label="{{ __('Start Date') }}"
                    />
                    <x-input
                        type="date"
                        wire:model="task.due_date"
                        label="{{ __('Due Date') }}"
                    />
                </div>
                <x-flux::state
                    class="w-full"
                    align="bottom-start"
                    :label="__('Task state')"
                    wire:model="task.state"
                    formatters="formatter.state"
                    available="availableStates"
                />
                <x-number
                    :label="__('Priority')"
                    wire:model="task.priority"
                    min="0"
                />
                <x-textarea
                    wire:model="task.description"
                    label="{{ __('Description') }}"
                />
                <x-select.styled
                    :label="__('Categories')"
                    wire:model="task.categories"
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
                    wire:model="task.users"
                    select="label:label|value:id"
                    unfiltered
                    :request="[
                        'url' => route('search', \FluxErp\Models\User::class),
                        'method' => 'POST',
                        'params' => [
                            'with' => 'media',
                        ]
                    ]"
                />
                <x-number
                    :label="__('Budget')"
                    wire:model="task.budget"
                    step="0.01"
                />
                <x-input
                    :label="__('Time Budget')"
                    wire:model.blur="task.time_budget"
                    :corner-hint="__('Hours:Minutes')"
                    placeholder="02:30"
                />
            </div>
            <div class="space-y-2.5">
                <h3
                    class="text-md text-secondary-700 dark:text-secondary-400 mt-4 whitespace-normal font-medium"
                >
                    {{ __('Additional Columns') }}
                </h3>
                <x-flux::additional-columns
                    :model="\FluxErp\Models\Task::class"
                    :id="$this->task->id"
                    wire="task.additionalColumns"
                />
            </div>
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('create-task-modal')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                x-on:click="$wire.save().then((task) => {
                    if (task) {
                        close();
                    }
                });"
            />
        </x-slot>
    </x-modal>
    <x-button
        color="indigo"
        x-on:click="$wire.resetTask(); $modalOpen('create-task-modal');"
        :text="__('Create Task')"
    />
</div>
