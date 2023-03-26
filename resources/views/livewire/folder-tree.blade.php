<div x-data="{selected: @entangle('selected').defer, selectedCollection: @entangle('selectedCollection').defer}">
    <x-modal.card z-index="z-10" x-bind:title="selected.mime_type + ' - ' + selected.human_readable_size" wire:model.defer="showDetails">
        <div class="w-full rounded-md">
            <div class="flex max-h-96 min-h-[12rem] w-full items-center justify-center rounded bg-gray-300 dark:bg-gray-700">
                <svg class="absolute z-0 h-12 w-12 text-gray-200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="currentColor" viewBox="0 0 640 512"><path d="M480 80C480 35.82 515.8 0 560 0C604.2 0 640 35.82 640 80C640 124.2 604.2 160 560 160C515.8 160 480 124.2 480 80zM0 456.1C0 445.6 2.964 435.3 8.551 426.4L225.3 81.01C231.9 70.42 243.5 64 256 64C268.5 64 280.1 70.42 286.8 81.01L412.7 281.7L460.9 202.7C464.1 196.1 472.2 192 480 192C487.8 192 495 196.1 499.1 202.7L631.1 419.1C636.9 428.6 640 439.7 640 450.9C640 484.6 612.6 512 578.9 512H55.91C25.03 512 .0006 486.1 .0006 456.1L0 456.1z"></path></svg>
                <div x-html="selected.preview" class="z-10 w-full rounded-lg p-5"></div>
            </div>
        </div>
        <div class="flex w-full justify-center py-5">
            <x-button primary :label="__('Download')" wire:click="download"/>
        </div>
        @if(auth()->user()->can('api.media.put'))
            <div class="space-y-5">
                <x-input :label="__('Filename')" x-model="selected.name" />
                <x-select wire:model.defer="selected.disk" :label="__('Disk')" :options="array_keys(config('filesystems.disks'))" />
            </div>
        @endif
        <x-errors />
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                @if(auth()->user()->can('api.media.{id}.delete'))
                <x-button x-show="selected.id" flat negative :label="__('Delete')" x-on:click="window.$wireui.confirmDialog({
                                                        title: '{{ __('Delete file') }}',
                                                        description: '{{ __('Do you really want to delete this file?') }}',
                                                        icon: 'error',
                                                        accept: {
                                                            label: '{{ __('Delete') }}',
                                                            method: 'delete',
                                                        },
                                                        reject: {
                                                            label: '{{ __('Cancel') }}',
                                                        }
                                                    }, '{{ $this->id }}')
                                                    " />
                @endif

                <div class="flex w-full justify-end">
                    <x-button flat :label="__('Cancel')" x-on:click="close" />
                    @if(auth()->user()->can('api.media.put'))
                        <x-button primary :label="__('Save')" wire:click="save" />
                    @endif
                </div>
            </div>
        </x-slot>
    </x-modal.card>

    @if(auth()->user()->can('api.media.put'))
        <x-modal.card z-index="z-10" wire:model.defer="showFolderDetails">
            <x-input x-bind:disabled="selectedCollection.is_static" :label="__('Foldername')" wire:model.defer="selectedCollection.name" />

            @if(auth()->user()->can('api.media.post'))
                <x-features.media.upload wire:model.defer="upload"/>
            @endif
            <x-slot name="footer">
                <div class="flex justify-between gap-x-4">
                    @if(auth()->user()->can('api.media.{id}.delete'))
                        <x-button flat x-show="! selectedCollection.is_static" negative :label="__('Delete')" x-on:click="window.$wireui.confirmDialog({
                                                            title: '{{ __('Delete folder') }}',
                                                            description: '{{ __('Do you really want to delete this folder and all containing files?') }}',
                                                            icon: 'error',
                                                            accept: {
                                                                label: '{{ __('Delete') }}',
                                                                method: 'deleteFolder',
                                                            },
                                                            reject: {
                                                                label: '{{ __('Cancel') }}',
                                                            }
                                                        }, '{{ $this->id }}')
                                                        " />
                    @endif

                    <div class="flex w-full justify-end">
                        <x-button flat :label="__('Cancel')" x-on:click="close" />
                        <x-button primary :label="__('Save')" wire:click="saveFolder" />
                    </div>
                </div>
            </x-slot>
        </x-modal.card>
    @endif
        <div wire:ignore.self
             x-data="
             {
                 tree: @entangle('tree').defer,
                 open: ['files'], addOrRemove(value) {
                        var index = this.open.indexOf(value);

                        if (index === -1) {
                            this.open.push(value);
                        } else {
                            this.open.splice(index, 1);
                        }
                    }
            }">
            <x-folder-tree :tree="$tree" />
        </div>
</div>
