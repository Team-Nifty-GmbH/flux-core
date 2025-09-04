<div class="flex grow justify-between gap-2" wire:ignore>
    <div class="w-full min-w-0 overflow-auto">
        <ul class="flex flex-col gap-1">
            <x-flux::checkbox-tree
                tree="$wire.getTree()"
                name-attribute="name"
                moved="$wire.moveItem(item, node?.is_new ? getNodePath(node, 'collection_name') : node?.collection_name)"
                sortable
                x-sort:item="isLeaf(childNode) ? childNode.id : childNode.collection_name"
            >
                <x-slot:beforeTree>
                    @section('folder-tree.before-tree')
                    @show
                </x-slot>
                <x-slot:afterTree>
                    @section('folder-tree.after-tree')
                    @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                        <x-button
                            color="secondary"
                            light
                            class="my-2 w-full whitespace-nowrap"
                            :text="__('Add folder')"
                            x-on:click="addFolder(null, {
                                    is_static: false,
                                    is_new: true,
                                    collection_name: 'new_folder',
                                    name: '{{ __('New folder') }}',
                                    children: [],
                                })"
                        />
                    @endcanAction

                    @show
                </x-slot>
                <div
                    class="flex w-full flex-col gap-3 lg:w-1/2"
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
                                uploadDisabled: '{{ __('Upload not allowed - Read Only') }}',
                                readyForUpload: '{{ __('Ready for upload') }}',
                                pending: '{{ __('pending') }}',
                            },
                        ),
                        previewSupported: true,
                        selectionProxy: {},
                        selection: {},
                        countChildren() {
                            return this.selectionProxy?.children?.length
                        },
                        saveFolder() {
                            $wire.save(this.selection).then(() => {
                                this.selectionProxy = JSON.parse(JSON.stringify(this.selection))

                                updateNode(this.selectionProxy)
                            })
                        },
                        treeSelect(level) {
                            // during file upload, do not allow folder change
                            if (this.isLoadingFiles.length !== 0) {
                                return
                            }

                            // on folder change, clear temp files - if confirmation is accepted
                            if (this.tempFilesId.length !== 0) {
                                $interaction('dialog')
                                    .wireable($wire.__instance.id)
                                    .warning(
                                        '{{ __('Selected files not submitted') }}',
                                        '{{ __('Selected files will be deleted on folder change') }}',
                                        'amber',
                                    )
                                    .confirm('{{ __('Confirm') }}', () => {
                                        this.clearFilesOnLeave()
                                        this.selectionProxy = level
                                        this.selection = JSON.parse(JSON.stringify(level))
                                        this.selected = true
                                        this.setCollection(this.selection?.collection_name)
                                    })

                                return
                            }

                            if (this.selection.id === level.id) {
                                this.selected = false
                                this.selectionProxy = {}
                                this.selection = {}
                                this.setCollection(null)

                                return
                            }

                            this.selectionProxy = level
                            this.selection = JSON.parse(JSON.stringify(level))
                            this.setCollection(this.selection?.collection_name)
                        },
                        filesArray: $wire.entangle('filesArray', true),
                        async uploadSuccess(multipleFileUpload) {
                            // on single file replace, replace selection - otherwise, add
                            const lastUploads = await $wire.get('latestUploads')

                            if (multipleFileUpload) {
                                lastUploads.forEach((file) => {
                                    this.selectionProxy.children.push(file)
                                    this.selection = JSON.parse(JSON.stringify(this.selectionProxy))
                                }, this)
                            } else {
                                this.selectionProxy.children = lastUploads
                                this.selection = JSON.parse(JSON.stringify(this.selectionProxy))
                            }
                        },
                    }"
                    x-on:folder-tree-select.window="treeSelect($event.detail)"
                >
                    <div
                        x-ref="upload"
                        x-show="! selection.file_name && selected"
                        class="flex w-full flex-col gap-3"
                        x-cloak
                    >
                        <div>
                            @section('folder-tree.upload.buttons')
                            @canAction(\FluxErp\Actions\Media\DeleteMediaCollection::class)
                                <x-button
                                    x-cloak
                                    x-show="! selected?.is_static"
                                    :text="__('Delete')"
                                    color="red"
                                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Folder')]) }}"
                                    wire:click="deleteCollection(getNodePath('collection_name')).then(() => {
                                                    try {
                                                            selected = null;
                                                            removeNode(selection.id);
                                                        } catch (error) {
                                                            console.error(error);
                                                        }
                                                    })"
                                />
                            @endcanAction

                            @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                                <x-button
                                    color="secondary"
                                    light
                                    x-cloak
                                    x-show="multipleFileUpload && !readOnly"
                                    :text="__('Add folder')"
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
                            @endcanAction

                            @canAction(\FluxErp\Actions\Media\DownloadMultipleMedia::class)
                                <x-button
                                    color="secondary"
                                    light
                                    loading
                                    :text="__('Download folder')"
                                    x-on:click="$wire.downloadCollection(getNodePath(selection, 'collection_name'))"
                                />
                            @endcanAction

                            @show
                        </div>
                        @section('folder-tree.upload.attributes')
                        @canAction(\FluxErp\Actions\Media\UpdateMedia::class)
                            <div
                                class="flex flex-col items-end justify-end space-y-3 md:flex-row md:space-x-3"
                            >
                                <div class="w-full p-0 md:flex-1">
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
                                    color="indigo"
                                    :text="__('Save')"
                                    x-on:click="saveFolder()"
                                />
                            </div>
                        @endcanAction

                        @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                            <div class="flex flex-col items-end">
                                <div class="mb-4 w-full">
                                    <input
                                        x-init="loadFilePond(countChildren)"
                                        id="filepond-drop"
                                        type="file"
                                    />
                                </div>
                                <x-button
                                    x-cloak
                                    x-show="tempFilesId.length !== 0 && isLoadingFiles.length === 0"
                                    :text="__('Save')"
                                    color="indigo"
                                    x-on:click="submitFiles(selected.is_new ? getNodePath(null, 'collection_name') : selected.collection_name, uploadSuccess)"
                                />
                            </div>
                        @endcanAction

                        @show
                    </div>
                    <div
                        x-show="selection.file_name && selected"
                        x-cloak
                        class="flex w-full flex-col gap-3"
                    >
                        <div class="pb-1.5">
                            <x-button
                                color="indigo"
                                :text="__('Download')"
                                x-on:click="$wire.download(selected.id)"
                            />
                            @canAction(\FluxErp\Actions\Media\DeleteMedia::class)
                                <x-button
                                    color="red"
                                    :text="__('Delete')"
                                    wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Media')]) }}"
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
                            @endcanAction
                        </div>
                        <div class="flex flex-col gap-1.5">
                            @section('folder-tree.upload.media')
                            @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                                <x-input
                                    :text="__('Name')"
                                    disabled
                                    x-model="selection.name"
                                />
                                <x-input
                                    :label="__('Path')"
                                    disabled
                                    x-model="selection.collection_name"
                                />
                                <x-input
                                    :label="__('File type')"
                                    disabled
                                    x-bind:value="selection.file_name?.split('.').pop()"
                                />
                                <x-input
                                    :label="__('MIME-Type')"
                                    disabled
                                    x-bind:value="selection.mime_type"
                                />
                                <x-input
                                    :label="__('Size')"
                                    disabled
                                    x-bind:value="window.fileSizeHumanReadable(selection?.size)"
                                />
                                <x-input
                                    :label="__('File')"
                                    disabled
                                    x-bind:value="selection.file_name"
                                />
                                <x-input
                                    :label="__('Disk')"
                                    disabled
                                    x-bind:value="selection.disk"
                                />
                                <x-input
                                    :label="__('Link')"
                                    readonly
                                    x-ref="originalLink"
                                    type="text"
                                    x-bind:value="selection.original_url"
                                >
                                    <x-slot:suffix>
                                        <div
                                            class="absolute inset-y-0 right-0 flex items-center p-0.5"
                                        >
                                            <x-button
                                                x-cloak
                                                x-show="previewSupported"
                                                x-on:click="$openDetailModal(selection.original_url)"
                                                icon="eye"
                                                class="h-full rounded-l-md"
                                                color="indigo"
                                                squared
                                            />
                                            <x-button
                                                x-on:click="$refs.originalLink.select(); document.execCommand('copy');"
                                                class="h-full rounded-r-md"
                                                icon="clipboard-document"
                                                color="indigo"
                                                squared
                                            />
                                        </div>
                                    </x-slot>
                                </x-input>
                            @endcanAction

                            <object
                                x-on:load="previewSupported = true"
                                x-on:error="previewSupported = false"
                                x-on:click="$openDetailModal(selection.original_url)"
                                class="cursor-pointer object-contain"
                                x-bind:type="selection.mime_type"
                                x-bind:data="selection.original_url + '#zoom=85&scrollbar=0&toolbar=0&navpanes=0'"
                                width="100%"
                                height="200px"
                            >
                                <div
                                    class="flex h-48 w-full items-center justify-center bg-gray-200 text-gray-400"
                                >
                                    {{ __('Your browser does not support preview for this file.') }}
                                </div>
                            </object>
                            @show
                        </div>
                    </div>
                </div>
            </x-flux::checkbox-tree>
        </ul>
    </div>
</div>
