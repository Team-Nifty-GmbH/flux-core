<div class="space-y-5"
     x-data="{
        formatter: @js(\FluxErp\Models\Project::typeScriptAttributes()),
     }"
     wire:key="{{ uniqid() }}"
>
    <div  id="project-task-modal">
        <x-modal max-width="4xl">
            <x-card>
                <livewire:project-task.project-task :project-id="$this->project['id'] ?? null" />
            </x-card>
        </x-modal>
    </div>
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
    <livewire:data-tables.project-tasks-list
        cache-key="project.general.project-tasks-list"
        :headline="__('Tasks')"
        :filters="[
            [
                'project_id',
                '=',
                $this->project['id'],
            ],
        ]"
    />
</div>
