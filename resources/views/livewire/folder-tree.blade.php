<div x-data="{
                ...folderTree(),
                levels: [],
                loadLevels() {
                    $wire.getTree().then((result) => this.levels = result);
                },
                loadModel(modelType, modelId) {
                    $wire.set('modelType', modelType, true);
                    $wire.set('modelId', modelId, true);
                    this.loadLevels();
                },
                selectionProxy: {},
                selection: {},
                selected: false,
                treeSelect(level) {
                    if (this.selection.id === level.id) {
                        this.selected = false;
                        this.selectionProxy = {};
                        this.selection = {};

                        return;
                    }

                    this.selectionProxy = level;
                    this.selection = JSON.parse(JSON.stringify(level));
                    this.selected = true;
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
                    return list.map ( item => { return {...item} }).filter ( item => {
                        if ( 'children' in item ) {
                            item.children = this.recursiveRemove ( item.children, id );
                        }
                        return item.id !== id;
                    });
                },
                isFolder(level) {
                    return level.hasOwnProperty('children') && ! level.hasOwnProperty('file_name');
                },
                isDropping: false,
                isUploading: false,
                progress: 0,
                filesArray: $wire.entangle('filesArray'),
                handleFileSelect(event) {
                    if (event.target.files.length) {
                        this.uploadFiles(event.target.files, event)
                    }
                },
                handleFileDrop(event) {
                    if (event.dataTransfer.files.length > 0) {
                        this.uploadFiles(event.dataTransfer.files, event)
                    }
                },
                uploadError(message) {
                    this.isUploading = false;
                    this.progress = 0;
                    window.$wireui.notify({
                        title: '{{  __('File upload failed') }}',
                        description: message ? message : '{{ __('Your file upload failed. Please try again.') }}',
                        icon: 'error'
                    });
                },
                uploadSuccess(success, files) {
                    this.isUploading = false
                    this.progress = 0
                    this.showLevel(null, this.selectionProxy);
                    $wire.get('latestUploads').forEach((file) => {
                        if(file.status === 201) {
                            this.selectionProxy.children.push(file.data);
                            this.selection = JSON.parse(JSON.stringify(this.selectionProxy));
                        } else {
                            this.uploadError(Object.values(file.errors).join(', '));
                        }
                    });
                },
                uploadProgress(progress) {
                    this.progress = progress
                },
                uploadFiles(files, event) {
                    this.isUploading = true;
                    let $this = this;
                    $wire.set('collection', this.selectionProxy.collection_name);
                    $wire.uploadMultiple('files', files,
                        function (success) {
                            let uploadedFiles = event.target.files?.length ? event.target.files : event.dataTransfer.files;
                            $this.uploadSuccess(success, uploadedFiles);
                        },
                        function(error) {
                           $this.uploadError();
                        },
                        function (event) {
                            $this.uploadProgress(event);
                        }
                    )
                },
                removeUpload(index) {
                    $wire.removeUpload('files', index)
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

                    if(parent) {
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
                            execute: () => {
                                $wire.delete(level.id).then((success) => {
                                    this.selected = false;
                                    this.selection = {};

                                    this.levels = this.recursiveRemove(this.levels, level.id);
                                });
                            },
                        },
                        reject: {
                            label: '{{ __('Cancel') }}',
                        }
                    }, '{{ $this->id }}');
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

                                    this.levels = this.recursiveRemove(this.levels, level.id);
                                });
                            },
                        },
                        reject: {
                            label: '{{ __('Cancel') }}',
                        }
                    }, '{{ $this->id }}');
                },
            }"
     class="flex gap-2 justify-between"
     x-init="loadLevels();"
     x-on:folder-tree-select="treeSelect($event.detail)"
>
    <div class="min-w-0 overflow-auto">
        <ul class="flex flex-col gap-1" wire:ignore>
            <template x-for="(level, i) in levels" :key="level.id">
                <li x-html="renderLevel(level, i)"></li>
            </template>
            @can('api.media.post')
                <li>
                    <x-button class="w-full" outline :label="__('Add folder')" x-on:click="addFolder(levels, null)" />
                </li>
            @endcan
        </ul>
    </div>
    <div class="w-1/2 max-w-[96rem] flex flex-col gap-3">
        <div x-show="! selection.file_name && selected" class="flex flex-col gap-3" x-cloak>
            <div>
                @can('api.media.post')
                    <x-button x-show="! selection.is_static" negative :label="__('Delete')" x-on:click="deleteFolder(selection)" />
                @endcan
                @can('api.media.post')
                    <x-button :label="__('Add folder')" x-on:click="addFolder(selectionProxy.children, selection)" />
                @endcan
                <x-button :label="__('Download folder')" x-on:click="$wire.downloadCollection(selection.id)" />
            </div>
            @can('api.media.put')
                <x-input x-bind:disabled="selection.is_static" :label="__('Name')" x-model="selection.name" />
            @endcan
            @can('api.media.post')
                <div class="relative flex flex-col items-center justify-center"
                     x-on:drop="isDropping = false"
                     x-on:drop.prevent="handleFileDrop($event)"
                     x-on:dragover.prevent="isDropping = true"
                     x-on:dragleave.prevent="isDropping = false"
                >
                    <div class="absolute top-0 bottom-0 left-0 right-0 z-30 flex items-center justify-center bg-blue-500 opacity-90"
                         x-show="isDropping"
                    >
                        <span class="text-3xl text-white">{{ __('Release file to upload!') }}</span>
                    </div>
                    <label class="order-2 flex w-full cursor-pointer select-none flex-col items-center justify-center rounded-md border-dashed border-gray-300 bg-gray-50 p-10 shadow hover:bg-slate-50"
                           for="file-upload"
                    >
                        <div class="pb-3">
                            <x-heroicons name="arrow-up-on-square" class="h-12 w-12" />
                        </div>
                        <p>{{ __('Click here to select files to upload') }}</p>
                        <em class="italic text-slate-400">{{ __('(Or drag files to the page)') }}</em>
                        <div class="mt-3 h-[2px] w-1/2 bg-gray-200" x-show="isUploading">
                            <div
                                class="h-[2px] bg-blue-500"
                                style="transition: width 1s"
                                x-bind:style="`width: ${progress}%;`"
                            >
                            </div>
                        </div>
                    </label>
                    <input type="file" id="file-upload"  class="hidden" multiple x-on:change="handleFileSelect($event)"/>
                </div>
            @endcan
        </div>
        <div x-show="selection.file_name && selected" x-cloak class="flex flex-col gap-3">
            <div class="pb-1.5">
                <x-button primary :label="__('Download')" x-on:click="$wire.download(selection.id)" />
                @can('api.media.delete')
                    <x-button negative :label="__('Delete')" x-on:click="deleteFile(selection)" />
                @endcan
            </div>
            <div class="flex flex-col gap-1.5">
                @can('api.media.put')
                    <x-input :label="__('Name')" x-model="selection.name" />
                    <x-input :label="__('File type')" disabled x-bind:value="selection.file_name?.split('.').pop()" />
                    <x-input :label="__('MIME-Type')" disabled x-bind:value="selection.mime_type" />
                    <x-input :label="__('Size')" disabled x-bind:value="convertSize(selection?.size)" />
                    <x-input :label="__('File')" disabled x-bind:value="selection.file_name" />
                    <x-input :label="__('Disk')" disabled x-bind:value="selection.disk" />
                    <x-input x-show="selection?.disk === 'public'"
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
                @endcan
                <embed class="object-contain"
                    x-bind:type="selection.mime_type"
                    x-bind:src="selection.original_url"
                    width="100%"
                    height="200px"
                />
            </div>
        </div>
        @can('api.media.put')
            <div x-show="selected" class="w-full flex justify-end">
                <x-button primary :label="__('Save')" x-on:click="save()" />
            </div>
        @endcan
    </div>
</div>
