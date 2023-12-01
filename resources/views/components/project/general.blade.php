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
    @if($this->additionalColumns ?? false)
        <x-card class="space-y-2.5" :title="__('Additional columns')">
            @foreach($this->additionalColumns as $additionalColumn)
                @if($additionalColumn['values'] ?? false)
                    <x-select
                        x-bind:readonly="!edit"
                        x-model="project.{{ $additionalColumn['name'] }}"
                        :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                        :options="$additionalColumn['values']"
                    />
                @elseif($additionalColumn['field_type'] === 'checkbox')
                    <x-checkbox
                        x-bind:disabled="!edit"
                        x-model="project.{{ $additionalColumn['name'] }}"
                        :label="__($additionalColumn['label'] ?? $additionalColumn['name'])"
                    />
                @else
                    <x-input type="{{ $additionalColumn['field_type'] ?? 'text' }}"
                             x-bind:readonly="!edit"
                             x-bind:disabled="!edit"
                             x-model="project.{{ $additionalColumn['name'] }}"
                             :label="__($additionalColumn['label'])"
                    />
                @endif
            @endforeach
        </x-card>
    @endif
    <div>
        <livewire:data-tables.task-list
            cache-key="project.general.task-list"
            :headline="__('Tasks')"
            :filters="[
            [
                'project_id',
                '=',
                $this->project->id,
            ],
        ]"
        />
        <x-modal x-on:new-task="open()" max-width="4xl">
            <x-card>
                <livewire:task.task :project-id="$this->project->id ?? null" />
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
                                window.location.href = baseRoute.replace(':id', $wire.project.id);
                            }
                        });"
                        />
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
    </div>
</div>
