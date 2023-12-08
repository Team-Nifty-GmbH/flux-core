<div x-data="{
    task: $wire.$entangle('task', false),
    edit: true,
}">
    <div id="new-task-modal">
        <x-modal name="task-form-modal">
            <x-card>
                <x-tabs
                    wire:model.live="taskTab"
                    :$tabs
                />
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
                            x-on:click="$wire.save().then((task) => {
                                if (task) {
                                    close();
                                }
                            });"
                        />
                    </div>
                </x-slot:footer>
            </x-card>
        </x-modal>
    </div>
    <div wire:ignore x-on:data-table-row-clicked="$wire.fillForm($event.detail.id)">
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
