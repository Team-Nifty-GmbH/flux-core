<div>
    <x-modal name="edit-work-time-type">
        <x-card>
            <div class="flex flex-col gap-4">
                <x-input wire:model="workTimeType.name" :label="__('Name')" />
                <x-toggle wire:model="workTimeType.is_billable" :label="__('Is Billable')" />
            </div>
            <x-slot:footer>
                <div class="flex justify-between gap-x-4">
                    @if(resolve_static(\FluxErp\Actions\WorkTimeType\DeleteWorkTimeType::class, 'canPerformAction', [false]))
                        <div x-bind:class="$wire.workTimeType.id > 0 || 'invisible'">
                            <x-button
                                flat
                                negative
                                :label="__('Delete')"
                                x-on:click="close"
                                wire:click="delete().then((success) => { if(success) close()})"
                                wire:confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Work Time Type')]) }}"
                            />
                        </div>
                    @endif
                    <div class="flex">
                        <x-button flat :label="__('Cancel')" x-on:click="close"/>
                        <x-button primary :label="__('Save')" wire:click="save().then((success) => { if(success) close()})"/>
                    </div>
                </div>
            </x-slot:footer>
        </x-card>
    </x-modal>
    <div wire:ignore>
        @include('tall-datatables::livewire.data-table')
    </div>
</div>
