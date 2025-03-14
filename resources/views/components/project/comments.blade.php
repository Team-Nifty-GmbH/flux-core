<x-card class="!px-0 !py-0" x-data wire:key="{{ uniqid() }}">
    <livewire:features.comments.comments
        :model-type="\FluxErp\Models\Project::class"
        :model-id="$this->project->id"
    />
</x-card>
