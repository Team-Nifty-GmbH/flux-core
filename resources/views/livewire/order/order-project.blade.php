<div class="flex flex-col gap-4">
    @canAction(\FluxErp\Actions\Project\CreateProject::class)
        <x-toggle
            :label="__('Existing project')"
            wire:model="existingProject"
        />
    @endCanAction
    <div x-cloak x-show="$wire.existingProject">
        <x-select.styled
            wire:model="projectId"
            :label="__('Project')"
            :request="[
                'url' => route('search', \FluxErp\Models\Project::class),
                'method' => 'POST',
            ]"
            select="label:label|value:id"
        />
    </div>
    @canAction(\FluxErp\Actions\Project\CreateProject::class)
        <div class="flex flex-col gap-4" x-cloak x-show="! $wire.existingProject">
            <x-input :label="__('Project Name')" wire:model="form.name" />
            <x-date without-time :label="__('Start Date')" wire:model="form.start_date" />
            <x-date without-time :label="__('End Date')" wire:model="form.end_date" />
        </div>
    @endCanAction
</div>
<x-slot:footer>
    <div class="flex justify-end gap-x-4">
        <x-button color="secondary" light flat :text="__('Cancel')" x-on:click="$modalClose('create-tasks')" />
        <x-button spinner="createTasks" color="indigo" :text="__('Save')" wire:click="save().then((success) => {if(success) $modalClose('create-tasks');})" />
    </div>
</x-slot:footer>
