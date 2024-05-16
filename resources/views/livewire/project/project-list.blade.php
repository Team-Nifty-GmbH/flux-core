<div>
    <x-modal name="edit-project" x-on:create-project.window="$wire.resetForm(); open();">
        <x-card>
            <div x-data="{edit: true, formatter: @js(resolve_static(\FluxErp\Models\Project::class, 'typeScriptAttributes'))}">
                <x-project.edit/>
            </div>
            <x-slot:footer>
                <div class="flex justify-end">
                    <x-button
                        flat
                        :label="__('Cancel')"
                        x-on:click="close()"
                    />
                    <x-button
                        primary
                        :label="__('Save')"
                        x-on:click="$wire.save().then((project) => {
                            if (project) {
                                close();
                                let baseRoute = '{{ route('projects.id', ['id' => ':id']) }}';
                                Livewire.navigate(baseRoute.replace(':id', $wire.project.id));
                            }
                        });"
                    />
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
</div>
