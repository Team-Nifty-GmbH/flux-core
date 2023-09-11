<div
    class="space-y-5"
    x-data="{
        ...folderTree(),
        levels: [],
        projectTaskCategories: [],
        openFolders: $wire.entangle('openCategories'),
        selectAttributes(obj) {
            return `x-bind:disabled='projectTaskCategories.includes(level.id) || ! edit'`;
        },
        loadLevels(id = null) {
            $wire.loadCategories(id).then((result) => this.levels = result);
            $wire.loadProjectTaskCategories(this.project.id).then((result) => this.projectTaskCategories = result);
        }
    }"
    x-model="project.categories"
    x-modelable="project.categories"
    x-init="loadLevels(project.category_id)"
>
    <x-errors />
    <x-input x-bind:readonly="!edit" x-model="project.project_name" label="{{ __('Name') }}" />
    <x-input x-bind:readonly="!edit" x-model="project.display_name" label="{{ __('Display name') }}" />
    <div class="flex justify-between">
        <x-state
            class="w-full"
            align="left"
            :label="__('Project state')"
            wire:model="project.state"
            formatters="formatter.state"
            avialable="availableStates"
        />
        <x-input type="date" x-bind:readonly="!edit" x-model="project.deadline" label="{{ __('Deadline') }}" />
        <x-input type="date" x-bind:readonly="!edit" x-model="project.release_date" label="{{ __('Release date') }}" />
    </div>
    <x-textarea x-bind:readonly="!edit" x-model="project.description" label="{{ __('Description') }}" />
    <x-model-select
        x-model="project.category_id"
        :label="__('Categories')"
        option-value="id"
        option-label="label"
        option-description="description"
        x-on:selected="loadLevels($event.detail.value)"
        :disabled="$this->project['tasks_count'] ?? false"
        :async-data="[
                'api' => route('search', \FluxErp\Models\Category::class),
                'method' => 'POST',
                'params' => [
                    'where' => [
                        [
                            'model_type',
                            '=',
                            \FluxErp\Models\Project::class,
                        ]
                    ],
                ],
            ]"
    ></x-model-select>
    <div class="pt-1.5">
        <ul wire:ignore class="flex flex-col gap-1">
            <template x-for="(level, i) in levels">
                <li x-html="renderLevel(level, i)"></li>
            </template>
        </ul>
    </div>
</div>
