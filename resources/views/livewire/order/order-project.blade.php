<x-card>
    <div class="flex flex-col gap-4">
        @canAction(\FluxErp\Actions\Project\CreateProject::class)
            <x-toggle
                :label="__('Existing project')"
                wire:model="existingProject"
            />
        @endCanAction
        <div x-cloak x-show="$wire.existingProject">
            <x-select
                wire:model="projectId"
                :label="__('Project')"
                :async-data="[
                    'api' => route('search', \FluxErp\Models\Project::class),
                    'method' => 'POST',
                ]"
                option-value="id"
                option-label="name"
            />
        </div>
        @canAction(\FluxErp\Actions\Project\CreateProject::class)
            <div class="flex flex-col gap-4" x-cloak x-show="! $wire.existingProject">
                <x-input :label="__('Project Name')" wire:model="form.name" />
                <x-datetime-picker without-time :label="__('Start Date')" wire:model="form.start_date" />
                <x-datetime-picker without-time :label="__('End Date')" wire:model="form.end_date" />
            </div>
        @endCanAction
    </div>
    <x-slot:footer>
        <div class="flex justify-end gap-x-4">
            <x-button flat :label="__('Cancel')" x-on:click="close" />
            <x-button spinner="createTasks" primary :label="__('Save')" wire:click="save().then((success) => {if(success) close();})" />
        </div>
    </x-slot:footer>
</x-card>
