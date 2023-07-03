<x-card class="!py-0 !px-0" x-data wire:key="{{ uniqid() }}">
    <livewire:features.comments.comments :model-type="\FluxErp\Models\Project::class" :model-id="$this->project['id']" />
</x-card>
