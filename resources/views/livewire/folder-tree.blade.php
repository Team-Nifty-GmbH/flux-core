<div class="flex gap-2 justify-between grow" wire:ignore>
    <div class="min-w-0 overflow-auto w-full">
        <ul class="flex flex-col gap-1">
            <x-flux::checkbox-tree
                tree="$wire.getTree()"
                name-attribute="name"
            >
                <x-slot:afterTree>
                    @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                        <x-button
                            class="w-full whitespace-nowrap my-2"
                            :label="__('Add folder')"
                            x-on:click="addFolder(null, {
                                is_static: false,
                                is_new: true,
                                collection_name: 'new_folder',
                                name: '{{ __('New folder') }}',
                                children: [],
                            })"
                        />
                    @endCanAction
                </x-slot:afterTree>
                <div class="w-1/2 flex flex-col gap-3"
                    x-data="{
                        ...filePond(
                            $wire,
                            $refs.upload,
                            '{{ Auth::user()?->language?->language_code }}',
                            {
                                title: '{{ __('File will be replaced') }}',
                                description: '{{ __('Do you want to proceed?') }}',
                                labelAccept: '{{ __('Accept') }}',
                                labelReject: '{{ __('Undo') }}',
                            },
                            {
                                uploadDisabled:'{{ __('Upload not allowed - Read Only') }}',
                            }
                        ),
                        selectionProxy: {},
                        selection: {},
                        countChildren() {
                            return this.selectionProxy?.children?.length;
                        },
                        saveFolder() {
                            $wire.save(this.selection).then(() => {
                                this.selectionProxy = JSON.parse(JSON.stringify(this.selection));

                                updateNode(this.selectionProxy);
                            });
                        },
                        treeSelect(level) {
                            // during file upload, do not allow folder change
                            if (this.isLoadingFiles.length !== 0){
                                return;
                            }

                            // on folder change, clear temp files - if confirmation is accepted
                            if (this.tempFilesId.length !== 0) {
                                window.$wireui.confirmDialog({
                                    title: '{{ __('Selected files not submitted') }}',
                                    description: '{{ __('Selected files will be deleted on folder change') }}',
                                    icon: 'warning',
                                    accept: {
                                        label: '{{ __('Confirm') }}',
                                        execute: () => {
                                            this.clearFilesOnLeave();
                                            this.selectionProxy = level;
                                            this.selection = JSON.parse(JSON.stringify(level));
                                            this.selected = true;
                                            this.setCollection(this.selection?.collection_name);
                                        },
                                    },
                                    reject: {
                                        label: '{{ __('Cancel') }}',
                                    }
                                }, $wire.__instance.id);

                                return;
                            }

                            if (this.selection.id === level.id) {
                                this.selected = false;
                                this.selectionProxy = {};
                                this.selection = {};
                                this.setCollection(null);

                                return;
                            }

                            this.selectionProxy = level;
                            this.selection = JSON.parse(JSON.stringify(level));
                            this.setCollection(this.selection?.collection_name);
                        },
                        filesArray: $wire.entangle('filesArray', true),
                        async uploadSuccess(multipleFileUpload) {
                            // on single file replace, replace selection - otherwise, add
                            const lastUploads = await $wire.get('latestUploads');

                            if(multipleFileUpload) {
                                lastUploads.forEach((file) => {
                                    this.selectionProxy.children.push(file);
                                    this.selection = JSON.parse(JSON.stringify(this.selectionProxy));
                                }, this);
                            } else {
                                this.selectionProxy.children = lastUploads;
                                this.selection = JSON.parse(JSON.stringify(this.selectionProxy));
                            }
                        }
                    }"
                    x-on:folder-tree-select.window="treeSelect($event.detail)"
                >
                    <div x-ref="upload" x-show="! selection.file_name && selected" class="flex flex-col gap-3" x-cloak>
                        <div>
                            @canAction(\FluxErp\Actions\Media\DeleteMediaCollection::class)
                                <x-button
                                    x-cloak
                                    x-show="! selected?.is_static"
                                    :label="__('Delete')"
                                    negative
                                    wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Folder')]) }}"
                                    wire:click="deleteCollection(getNodePath('collection_name')).then(() => {
                                            try {
                                                    selected = null;
                                                    removeNode(selection.id);
                                                } catch (error) {
                                                    console.error(error);
                                                }
                                            })"
                                />
                            @endCanAction
                            @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                                <x-button
                                    x-cloak
                                    x-show="multipleFileUpload && !readOnly"
                                    :label="__('Add folder')"
                                    x-on:click="addFolder(
                                        selected,
                                        {
                                            is_static: false,
                                            is_new: true,
                                            collection_name: 'new_folder',
                                            name: '{{ __('New folder') }}',
                                            children: [],
                                        }
                                    )"
                                />
                            @endCanAction
                            <x-button
                                spinner
                                :label="__('Download folder')"
                                x-on:click="$wire.downloadCollection(getNodePath())"
                            />
                        </div>
                        @canAction(\FluxErp\Actions\Media\UpdateMedia::class)
                            <div class="flex flex-col space-y-3 md:flex-row  md:space-x-3 items-end justify-end">
                                <div class="md:flex-1 w-full p-0">
                                    <x-input
                                        class="flex-1"
                                        x-bind:disabled="selected?.is_static"
                                        :label="__('Name')"
                                        x-model="selection.name"
                                    />
                                </div>
                                <x-button
                                    x-cloak
                                    x-show="! selected?.is_static"
                                    primary
                                    :label="__('Save')"
                                    x-on:click="saveFolder()"
                                />
                            </div>
                        @endCanAction
                        @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                            <div class="flex flex-col items-end">
                                <div class="w-full mb-4">
                                    <input x-init="loadFilePond(countChildren)" id="filepond-drop" type="file"/>
                                </div>
                                <x-button
                                    x-cloak
                                    x-show="tempFilesId.length !== 0 && isLoadingFiles.length === 0"
                                    :label="__('Save')"
                                    primary
                                    x-on:click="submitFiles(getNodePath(null, 'collection_name'), uploadSuccess)"
                                />
                            </div>
                        @endCanAction
                    </div>
                    <div x-show="selection.file_name && selected" x-cloak class="flex flex-col gap-3">
                        <div class="pb-1.5">
                            <x-button primary :label="__('Download')" x-on:click="$wire.download(selected.id)"/>
                            @if(resolve_static(\FluxErp\Actions\Media\DeleteMedia::class, 'canPerformAction', [false]))
                                <x-button
                                    negative
                                    :label="__('Delete')"
                                    wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Media')]) }}"
                                    wire:click="delete(selected.id).then(() => {
                                        try {
                                                this.selected = null;
                                                this.selection = {};
                                                removeNode(selection.id);
                                            } catch (error) {
                                                console.error(error);
                                            }
                                        })"
                                />
                            @endif
                        </div>
                        <div class="flex flex-col gap-1.5">
                            @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                                <x-input :label="__('Name')" disabled x-model="selection.name"/>
                                <x-input :label="__('Path')" disabled x-model="selection.collection_name"/>
                                <x-input :label="__('File type')" disabled x-bind:value="selection.file_name?.split('.').pop()"/>
                                <x-input :label="__('MIME-Type')" disabled x-bind:value="selection.mime_type"/>
                                <x-input :label="__('Size')" disabled x-bind:value="window.fileSizeHumanReadable(selection?.size)"/>
                                <x-input :label="__('File')" disabled x-bind:value="selection.file_name"/>
                                <x-input :label="__('Disk')" disabled x-bind:value="selection.disk"/>
                                <x-input
                                    x-show="selection?.disk === 'public'"
                                    :label="__('Link')"
                                    readonly
                                    x-ref="originalLink"
                                    type="text"
                                    x-bind:value="selection.original_url"
                                >
                                    <x-slot:append>
                                        <div class="absolute inset-y-0 right-0 flex items-center p-0.5">
                                            <x-button
                                                x-on:click="$refs.originalLink.select(); document.execCommand('copy');"
                                                class="h-full rounded-r-md"
                                                icon="clipboard-copy"
                                                primary
                                                squared
                                            />
                                        </div>
                                    </x-slot:append>
                                </x-input>
                            @endCanAction
                            <object
                                class="object-contain"
                                x-bind:type="selection.mime_type"
                                x-bind:data="selection.original_url + '#zoom=85&scrollbar=0&toolbar=0&navpanes=0'"
                                width="100%"
                                height="200px"
                            >
                                <div class="flex items-center justify-center w-full h-48 bg-gray-200 text-gray-400">
                                    {{ __('Your browser does not support preview for this file.') }}
                                </div>
                            </object>
                        </div>
                    </div>
                </div>
            </x-flux::checkbox-tree>
        </ul>
    </div>
</div>
