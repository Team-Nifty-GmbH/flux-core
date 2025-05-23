<div>
    <x-modal
        id="edit-project"
        x-on:open="$focusOn('project-name')"
        x-on:create-project.window="$wire.resetForm(); $modalClose('edit-project');"
    >
        <div
            x-data="{ edit: true, formatter: @js(resolve_static(\FluxErp\Models\Project::class, 'typeScriptAttributes')) }"
        >
            <x-flux::project.edit />
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('edit-project')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                x-on:click="$wire.save().then((project) => {
                    if (project) {
                        $modalClose('edit-project');
                        let baseRoute = '{{ route('projects.id', ['id' => ':id']) }}';
                        Livewire.navigate(baseRoute.replace(':id', $wire.project.id));
                    }
                });"
            />
        </x-slot>
    </x-modal>
</div>
