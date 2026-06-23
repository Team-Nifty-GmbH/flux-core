<div class="flex grow justify-between gap-2" wire:ignore>
    <div class="w-full min-w-0 overflow-auto">
        <ul class="flex flex-col gap-1">
            <x-flux::checkbox-tree
                tree="$wire.getTree()"
                name-attribute="name"
                :search-attributes="['name', 'file_name', 'collection_name']"
                moved="$wire.moveItem(item, node, item.slug ?? item.collection_name ?? getNodePath(item, 'slug'), node.slug ?? node.collection_name ?? getNodePath(node, 'slug'))"
                sortable
                with-search
                x-sort:item="childNode"
            >
                <x-slot:beforeTree>
                    @section('folder-tree.before-tree')
                    @show
                </x-slot:beforeTree>
                <x-slot:afterTree>
                    @section('folder-tree.after-tree')
                        @canAction(\FluxErp\Actions\MediaFolder\CreateMediaFolder::class)
                            <x-button
                                class="my-2 w-full whitespace-nowrap"
                                x-cloak
                                x-show="!$wire.isReadonly"
                                color="secondary"
                                light
                                :text="__('Add folder')"
                                x-on:click="$wire.saveFolder({name: '{{ __('New folder') }}'}).then((folder) => { if (folder) addFolder(null, folder); })"
                            />
                        @endcanAction
                        @stack('folder-tree-tree-actions')
                    @show
                </x-slot:afterTree>
                <div
                    class="flex w-full flex-col gap-3"
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
                        treeSelect(level) {
                            // during file upload, do not allow folder change
                            if (this.isLoadingFiles.length !== 0) {
                                return
                            }

                            // on folder change, clear temp files - if confirmation is accepted
                            if (this.tempFilesId.length !== 0) {
                                $tsui
                                    .interaction('dialog')
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
                                        this.setCollection(
                                            this.selection?.slug ?? this.selection?.collection_name,
                                            this.selection?.id,
                                        )

                                        if (! this.selection.file_name) {
                                            let path = this.getNodePath(this.selectionProxy, 'slug')
                                            this.selection.path = path
                                                ? path[path.length - 1]
                                                : null
                                        }
                                    })

                                return
                            }

                            if (this.selection.id === level.id) {
                                this.resetSelection()

                                return
                            }

                            this.selectionProxy = level
                            this.selection = JSON.parse(JSON.stringify(level))
                            this.setCollection(
                                this.selection?.slug ?? this.selection?.collection_name,
                                this.selection?.id,
                            )
                            if (! this.selection.file_name) {
                                let path = this.getNodePath(this.selectionProxy, 'slug')
                                this.selection.path = path ? path[path.length - 1] : null
                            }
                        },
                        async uploadSuccess(multipleFileUpload) {
                            // on single file replace, replace selection - otherwise, add
                            const lastUploads = await $wire.get('latestUploads')

                            if (multipleFileUpload) {
                                lastUploads.forEach((file) => {
                                    this.selectionProxy.children.push(JSON.parse(JSON.stringify(file)))
                                    this.selection = JSON.parse(JSON.stringify(this.selectionProxy))
                                }, this)
                            } else {
                                this.selectionProxy.children = lastUploads
                                this.selection = JSON.parse(JSON.stringify(this.selectionProxy))
                            }
                        },
                        resetSelection() {
                            this.selected = false
                            this.selectionProxy = {}
                            this.selection = {}
                            this.setCollection(null)
                        },
                        destroy() {
                            this.$el._refreshTreeObserver?.disconnect()
                        },
                    }"
                    x-init="
                        // Self-heal a stale modelId: when the embedding parent swaps the record
                        // renderlessly, the bound modelId never updates. Reload from the parent's
                        // live id whenever the tree becomes visible again (tab switch / reopen).
                        $el._refreshTreeObserver = new IntersectionObserver((entries) => {
                            if (entries.some((entry) => entry.isIntersecting)
                                && $resolveModelId() !== $wire.modelId) {
                                window.dispatchEvent(new CustomEvent('refresh-tree', { detail: { id: $resolveModelId() } }));
                            }
                        });
                        $el._refreshTreeObserver.observe($el);
                    "
                    x-on:folder-tree-select.window="treeSelect($event.detail)"
                    x-on:refresh-tree.window="
                        $wire.modelId = $event.detail.id;
                        resetSelection();
                        tree = await $wire.getTree();
                    "
                >
                    <div
                        class="flex w-full flex-col gap-3"
                        x-cloak
                        x-show="!selection.file_name && selected"
                        x-ref="upload"
                    >
                        <div class="flex flex-wrap gap-2">
                            @section('folder-tree.upload.buttons')
                                @canAction(\FluxErp\Actions\MediaFolder\DeleteMediaFolder::class)
                                    <x-button
                                        x-cloak
                                        x-show="!$wire.isReadonly && !readOnly"
                                        color="red"
                                        :text="__('Delete')"
                                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Folder')]) }}"
                                        x-on:click="
                                            $wire
                                                .deleteCollection(
                                                    selection.id,
                                                    getNodePath(
                                                        selectionProxy,
                                                        'slug',
                                                    ),
                                                )
                                                .then((success) => {
                                                    if (success) {
                                                        selected = null;
                                                        removeNode(
                                                            selection.id,
                                                        );
                                                    }
                                                })
                                        "
                                    />
                                @endcanAction
                                @canAction(\FluxErp\Actions\MediaFolder\CreateMediaFolder::class)
                                    <x-button
                                        x-cloak
                                        x-show="
                                            !$wire.isReadonly &&
                                            multipleFileUpload &&
                                            !readOnly
                                        "
                                        color="secondary"
                                        light
                                        :text="__('Add folder')"
                                        x-on:click="
                                            $wire
                                                .saveFolder({
                                                    parent_id: selection.id,
                                                    name: '{{ __('New folder') }}',
                                                    is_new: true,
                                                    children: [],
                                                })
                                                .then((folder) => {
                                                    if (folder) {
                                                        addFolder(selectionProxy, folder)
                                                    }
                                                })
                                        "
                                    />
                                @endcanAction
                                @canAction(\FluxErp\Actions\Media\DownloadMultipleMedia::class)
                                    <x-button
                                        color="secondary"
                                        light
                                        loading
                                        :text="__('Download folder')"
                                        wire:click="downloadCollection(selection.id, getNodePath(selection, 'slug'))"
                                    />
                                @endcanAction

                            @show
                            @stack('folder-tree-selection-actions')
                        </div>
                        @section('folder-tree.upload.attributes')
                            @canAction(\FluxErp\Actions\MediaFolder\UpdateMediaFolder::class)
                                <div
                                    class="flex flex-col gap-3 md:flex-row md:items-end"
                                >
                                    <div class="w-full md:flex-1">
                                        <x-input
                                            x-bind:disabled="
                                                $wire.isReadonly || readOnly
                                            "
                                            :label="__('Name')"
                                            x-model="selection.name"
                                        />
                                    </div>
                                    <x-button
                                        class="w-full md:w-auto"
                                        x-cloak
                                        x-show="!$wire.isReadonly && !readOnly"
                                        color="indigo"
                                        :text="__('Save')"
                                        x-on:click="
                                            $wire
                                                .saveFolder(selection)
                                                .then((folder) => {
                                                    if (folder) {
                                                        this.selectionProxy =
                                                            JSON.parse(
                                                                JSON.stringify(
                                                                    folder,
                                                                ),
                                                            );
                                                        updateNode(
                                                            this.selectionProxy,
                                                        );
                                                    }
                                                })
                                        "
                                    />
                                </div>
                            @endcanAction
                            @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                                <div
                                    class="flex flex-col"
                                    x-cloak
                                    x-show="!$wire.isReadonly && !readOnly"
                                >
                                    <div class="mb-4 w-full">
                                        <input
                                            x-init="loadFilePond(countChildren)"
                                            id="filepond-drop"
                                            type="file"
                                        />
                                    </div>
                                    <x-button
                                        class="w-full md:w-auto md:self-end"
                                        x-cloak
                                        x-show="
                                            tempFilesId.length !== 0 &&
                                            isLoadingFiles.length === 0
                                        "
                                        :text="__('Save')"
                                        color="indigo"
                                        x-on:click="mediaFolderId = Number.isInteger(selection.id) ? selection.id : null;
                                        submitFiles(
                                            getNodePath(selectionProxy, 'slug'),
                                            uploadSuccess,
                                            mediaFolderId ? '{{ morph_alias(\FluxErp\Models\MediaFolder::class) }}' : null,
                                            mediaFolderId
                                        )"
                                    />
                                </div>
                            @endcanAction

                        @show
                    </div>
                    <div
                        class="flex w-full flex-col gap-4"
                        x-cloak
                        x-show="selection.file_name && selected"
                    >
                        @section('folder-tree.upload.media')
                            <div
                                class="group relative flex h-72 cursor-pointer items-center justify-center overflow-hidden rounded-lg bg-gray-50 dark:bg-gray-800"
                                x-on:click="
                                    $nuxbe.openLightbox(
                                        selection.original_url,
                                        {
                                            mime: selection.mime_type,
                                            title: selection.name,
                                        },
                                    )
                                "
                            >
                                <template x-if="selection.thumb_url">
                                    <img
                                        x-bind:src="selection.thumb_url"
                                        x-bind:alt="selection.name"
                                        class="max-h-full max-w-full object-contain"
                                    />
                                </template>
                                <template x-if="!selection.thumb_url">
                                    <object
                                        x-on:load="previewSupported = true"
                                        x-on:error="previewSupported = false"
                                        class="pointer-events-none h-full w-full object-contain"
                                        x-bind:type="selection.mime_type"
                                        x-bind:data="
                                            selection.original_url +
                                            '#zoom=85&scrollbar=0&toolbar=0&navpanes=0&view=FitH'
                                        "
                                    >
                                        <div
                                            class="flex h-full w-full items-center justify-center text-sm text-gray-400"
                                        >
                                            {{ __('No preview available') }}
                                        </div>
                                    </object>
                                </template>
                                <div
                                    class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 transition group-hover:opacity-100"
                                >
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-white/95 px-4 py-1.5 text-sm font-medium text-gray-900"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.75"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                            />
                                        </svg>
                                        {{ __('Open preview') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1">
                                <div
                                    class="text-base font-medium break-words text-gray-900 dark:text-gray-100"
                                    x-text="selection.name"
                                ></div>
                                <div
                                    class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    <span
                                        x-text="
                                            selection.file_name
                                                ?.split('.')
                                                .pop()
                                                ?.toUpperCase()
                                        "
                                    ></span>
                                    <span>&middot;</span>
                                    <span
                                        x-text="
                                            $nuxbe.format.fileSize(
                                                selection?.size,
                                            )
                                        "
                                    ></span>
                                    <template x-if="selection.collection_name">
                                        <span
                                            class="inline-flex items-center gap-2"
                                        >
                                            <span>&middot;</span>
                                            <span
                                                class="truncate"
                                                x-text="
                                                    selection.collection_name
                                                "
                                            ></span>
                                        </span>
                                    </template>
                                </div>
                                <template x-if="selection.created_at">
                                    <div
                                        class="text-xs text-gray-500 dark:text-gray-400"
                                        x-text="
                                            $nuxbe.format.datetime(
                                                selection.created_at,
                                            )
                                        "
                                    ></div>
                                </template>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <x-button
                                    color="indigo"
                                    icon="arrow-down-tray"
                                    :text="__('Download')"
                                    x-on:click="$wire.download(selection.id)"
                                />
                                <x-button
                                    color="secondary"
                                    light
                                    icon="clipboard-document"
                                    :text="__('Copy link')"
                                    x-on:click="
                                        if (! navigator.clipboard?.writeText) {
                                            $tsui.interaction('toast')
                                                .error('{{ __('Error') }}', '{{ __('Failed to copy to clipboard. Please try again.') }}')
                                                .send()
                                            return
                                        }
                                        navigator.clipboard
                                            .writeText(selection.original_url)
                                            .then(() => {
                                                $tsui.interaction('toast')
                                                    .success('{{ __('Copied!') }}', '{{ __('Link copied to clipboard') }}')
                                                    .send()
                                            })
                                            .catch(() => {
                                                $tsui.interaction('toast')
                                                    .error('{{ __('Error') }}', '{{ __('Failed to copy to clipboard. Please try again.') }}')
                                                    .send()
                                            })
                                    "
                                />

                                @stack('folder-tree-file-actions')

                                @canAction(\FluxErp\Actions\Media\DeleteMedia::class)
                                    <div
                                        x-cloak
                                        x-show="!$wire.isReadonly && !readOnly"
                                        class="ml-auto"
                                    >
                                        <x-button
                                            color="red"
                                            light
                                            icon="trash"
                                            :text="__('Delete')"
                                            wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Media')]) }}"
                                            x-on:click="
                                                $wire
                                                    .delete(selection.id)
                                                    .then(() => {
                                                        try {
                                                            removeNode(
                                                                selection.id,
                                                            );
                                                            this.selected =
                                                                null;
                                                            this.selection = {};
                                                        } catch (error) {
                                                            console.error(
                                                                error,
                                                            );
                                                        }
                                                    })
                                            "
                                        />
                                    </div>
                                @endcanAction
                            </div>
                        @show
                    </div>
                </div>
            </x-flux::checkbox-tree>
        </ul>
    </div>
</div>
