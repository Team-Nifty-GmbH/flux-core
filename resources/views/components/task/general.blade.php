<div class="space-y-8 divide-y divide-gray-200">
    <div class="space-y-2.5"
         x-data="{
            formatter: @js(resolve_static(\FluxErp\Models\Task::class, 'typeScriptAttributes')),
         }"
    >
        <x-input x-bind:readonly="!edit" wire:model="task.name" label="{{ __('Name') }}" />
        <div x-show="task.id">
            <x-select
                :label="__('Project')"
                wire:model="task.project_id"
                option-value="id"
                option-label="label"
                option-description="description"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Project::class),
                ]"
            />
        </div>
        <x-select
            :label="__('Responsible User')"
            option-value="id"
            option-label="label"
            autocomplete="off"
            wire:model="task.responsible_user_id"
            :template="[
                'name'   => 'user-option',
            ]"
            :async-data="[
                'api' => route('search', \FluxErp\Models\User::class),
                'method' => 'POST',
                'params' => [
                    'with' => 'media',
                ]
            ]"
        />
        <div class="flex justify-between gap-x-4">
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
        </div>
        <x-state
            class="w-full"
            align="left"
            :label="__('Task state')"
            wire:model="task.state"
            formatters="formatter.state"
            available="availableStates"
        />
        <x-inputs.number x-bind:readonly="!edit" :label="__('Priority')" wire:model="task.priority" min="0" />
        <x-textarea x-bind:readonly="!edit" wire:model="task.description" label="{{ __('Description') }}" />
        <x-select
            :label="__('Assigned')"
            option-value="id"
            option-label="label"
            autocomplete="off"
            multiselect
            wire:model="task.users"
            :template="[
                'name'   => 'user-option',
            ]"
            :async-data="[
                'api' => route('search', \FluxErp\Models\User::class),
                'method' => 'POST',
                'params' => [
                    'with' => 'media',
                ]
            ]"
        />
        <div class="col-span-2">
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
    </div>
    <div class="space-y-2.5">
        <h3 class="font-medium whitespace-normal text-md text-secondary-700 dark:text-secondary-400 mt-4">
            {{ __('Additional Columns') }}
        </h3>
        <x-flux::additional-columns :model="\FluxErp\Models\Task::class" :id="$this->task->id" wire="task.additionalColumns"/>
    </div>
</div>
