<div
    class="space-y-5"
    x-data="{
        formatter: @js(resolve_static(\FluxErp\Models\Project::class, 'typeScriptAttributes')),
    }"
>
    @section('edit-card')
    <x-card class="space-y-2.5" :header="__('General')">
        <x-flux::project.edit :collapsed="true" />
    </x-card>
    @show
    <div>
        @section('content')
        <livewire:project.project-task-list
            cache-key="project.general.task-list"
            :headline="__('Tasks')"
            :projectId="$this->project->id"
        />
        @show
    </div>
</div>
