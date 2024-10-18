<div
    x-data="{
        ...folderTree(),
        ...filePond(
            $wire,$refs.upload,'{{ Auth::user()?->language?->language_code }}',
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
        async loadLevels() {
            this.levels = await $wire.getTree();
        },
        async loadModel(modelType, modelId) {
            await Promise.all([
                $wire.set('modelType', modelType, true),
                $wire.set('modelId', modelId, true),
                this.loadLevels()
            ]);
        },
        selectionProxy: {},
        selection: {},
        selected: false,
        countChildren() {
            return this.selectionProxy?.children?.length;
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
            this.selected = true;
            this.setCollection(this.selection?.collection_name);
        },
        convertSize(sizeBytes) {
            if (sizeBytes === null || sizeBytes === undefined) {
                return null
            }

            const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

            if (sizeBytes <= 0) {
                return '0B';
            }

            let i = 0;
            while (sizeBytes >= 1024 && i < units.length - 1) {
                sizeBytes /= 1024;
                i++;
            }

            const sizeStr = sizeBytes.toFixed(2);

            if (sizeStr.endsWith('.00')) {
                return sizeStr.slice(0, -3) + units[i];
            } else if (sizeStr.endsWith('0')) {
                return sizeStr.slice(0, -1) + units[i];
            }

            return sizeStr + units[i];
        },
        isSelected(level) {
            return this.selection.id === level.id;
        },
        itemAttributes() {
            return 'x-bind:class=\u0022isSelected(level) ? \'bg-primary-600 text-white fill-white\' : \'\'\u0022';
        },
        recursiveRemove (list, id) {
            return list.map(item => { return {...item} }).filter(item => {
                if ('children' in item) {
                    item.children = this.recursiveRemove(item.children, id);
                }

                return item.id !== id;
            });
        },
        isFolder(level) {
            return level.hasOwnProperty('children') && ! level.hasOwnProperty('file_name');
        },
        filesArray: $wire.entangle('filesArray', true),
        async uploadSuccess(multipleFileUpload) {
            this.showLevel(null, this.selectionProxy);
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
        },
        save() {
            $wire.save(this.selection).then(() => {
                Object.entries(this.selection).forEach(([key, value]) => {
                    this.selectionProxy[key] = value
                });
            });
        },
        addFolder(target, parent) {
            let id = Math.random().toString(36).substr(2, 9);
            let name = '{{ __('new_folder') }}' + '_' + target.length;
            let collectionName = parent ? parent.collection_name + '.' + name : name;

            if (parent) {
                this.showLevel(null, parent);
            }

            target.push({
                id: id,
                is_static: false,
                is_new: true,
                collection_name: collectionName,
                name: name,
                children: [],
            });

            this.treeSelect(target[target.length - 1]);
        },
        deleteFile(level, event) {
            window.$wireui.confirmDialog({
                title: '{{ __('Delete file') }}',
                description: '{{ __('Do you really want to delete this file?') }}',
                icon: 'error',
                accept: {
                    label: '{{ __('Delete') }}',
                    execute: async () => {
                         try {
                            await $wire.delete(level.id);
                            this.selected = false;
                            this.selection = {};

                            this.levels = this.recursiveRemove(this.levels, level.id);
                        } catch (error) {

                        }
                    },
                },
                reject: {
                    label: '{{ __('Cancel') }}',
                }
            }, $wire.__instance.id);
        },
        deleteFolder(level) {
            window.$wireui.confirmDialog({
                title: '{{ __('Delete folder') }}',
                description: '{{ __('Do you really want to delete this folder and all containing files?') }}',
                icon: 'error',
                accept: {
                    label: '{{ __('Delete') }}',
                    execute: () => {
                        $wire.deleteCollection(level.collection_name).then((success) => {
                            this.selected = false;
                            this.selection = {};
                            this.clearFilesOnLeave();
                            this.levels = this.recursiveRemove(this.levels, level.id);
                        });
                    },
                },
                reject: {
                    label: '{{ __('Cancel') }}',
                }
            }, $wire.__instance.id);
        },
    }"
    class="flex gap-2 justify-between"
    wire:ignore
    x-init="loadLevels();"
    x-on:folder-tree-select="treeSelect($event.detail)"
>
    <div class="min-w-0 overflow-auto">
        <ul class="flex flex-col gap-1">
            <template x-for="(level, i) in levels" :key="level.id">
                <li x-html="renderLevel(level, i)"></li>
            </template>
            @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                <li>
                    <x-button class="w-full" outline :label="__('Add folder')" x-on:click="addFolder(levels, null)"/>
                </li>
            @endCanAction
        </ul>
    </div>
    <div class="w-1/2 flex flex-col gap-3">
        <div x-ref="upload" x-show="! selection.file_name && selected" class="flex flex-col gap-3" x-cloak>
            <div>
                @canAction(\FluxErp\Actions\Media\DeleteMediaCollection::class)
                    <x-button
                        x-cloak
                        x-show="! selection.is_static"
                        :label="__('Delete')"
                        negative
                        x-on:click="deleteFolder(selection)"
                    />
                @endCanAction
                @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                    <x-button
                        x-cloak
                        x-show="multipleFileUpload && !readOnly"
                        :label="__('Add folder')"
                        x-on:click="addFolder(selectionProxy.children, selection)"
                    />
                @endCanAction
                <x-button
                    spinner
                    :label="__('Download folder')"
                    x-on:click="$wire.downloadCollection(selection.collection_name)"
                />
            </div>
            @canAction(\FluxErp\Actions\Media\UpdateMedia::class)
                <div class="flex flex-col space-y-3 md:flex-row  md:space-x-3 items-end justify-end">
                    <div class="md:flex-1 w-full p-0">
                        <x-input
                            class="flex-1"
                            x-bind:disabled="selection.is_static"
                            :label="__('Name')"
                            x-model="selection.name"
                        />
                    </div>
                    <x-button x-cloak x-show="!selection.is_static" primary :label="__('Save')" x-on:click="save()"/>
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
                        x-on:click="submitFiles(selectionProxy.collection_name, uploadSuccess)"
                    />
                </div>
            @endCanAction
        </div>
        <div x-show="selection.file_name && selected" x-cloak class="flex flex-col gap-3">
            <div class="pb-1.5">
                <x-button primary :label="__('Download')" x-on:click="$wire.download(selection.id)"/>
                @if(resolve_static(\FluxErp\Actions\Media\DeleteMedia::class, 'canPerformAction', [false]))
                    <x-button negative :label="__('Delete')" x-on:click="deleteFile(selection)"/>
                @endif
            </div>
            <div class="flex flex-col gap-1.5">
                @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                    <x-input :label="__('Name')" disabled x-model="selection.name"/>
                    <x-input :label="__('File type')" disabled x-bind:value="selection.file_name?.split('.').pop()"/>
                    <x-input :label="__('MIME-Type')" disabled x-bind:value="selection.mime_type"/>
                    <x-input :label="__('Size')" disabled x-bind:value="convertSize(selection?.size)"/>
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
</div>
