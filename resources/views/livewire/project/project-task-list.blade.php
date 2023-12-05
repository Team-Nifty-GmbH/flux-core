<div x-data="{
    task: $wire.$entangle('task', false),
    edit: true,
}">
    <div id="new-task-modal">
        <x-modal x-on:new-task.window="$wire.resetForm(); open()"
                 x-on:data-table-row-clicked.window="$wire.fillForm($event.detail.id); open()"
        >
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
    @include('tall-datatables::livewire.data-table')
</div>
