<div class="space-y-5"
     x-data="{
        formatter: @js(resolve_static(\FluxErp\Models\Project::class, 'typeScriptAttributes')),
     }"
>
    <x-card
        class="space-y-2.5"
        :title="__('General')">
        <x-flux::project.edit :collapsed="true"/>
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
