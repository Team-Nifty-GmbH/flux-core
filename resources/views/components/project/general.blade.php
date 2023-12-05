<div class="space-y-5"
     x-data="{
        formatter: @js(\FluxErp\Models\Project::typeScriptAttributes()),
     }"
>
    <x-card
        class="space-y-2.5"
        :title="__('General')">
        <x-project.edit />
    </x-card>
    <div>
        <livewire:project.project-task-list
            cache-key="project.general.task-list"
            :headline="__('Tasks')"
            :filters="[
                [
                    'project_id',
                    '=',
                    $this->project->id,
                ],
            ]"
            :projectId="$this->project->id"
        />
    </div>
</div>
