@php
    $wireModel = $attributes->wire('model')->value;
    $target = $wireModel . '.file';
@endphp
@props([
    'multiple' => true,
    'label' => null,
])
<div>
    <div x-data="{
        uploadObjectId: $id('file-upload'),
        isDropping: false,
        isUploading: false,
        progress: 0,
        handleFileSelect(event) {
            if (event.target.files.length) {
                this.uploadFiles(event.target.files, event);
            }
        },
        handleFileDrop(event) {
            if (event.dataTransfer.files.length > 0) {
                this.uploadFiles(event.dataTransfer.files, event);
            }
        },
        uploadError() {
            this.isUploading = false;
            this.progress = 0;
            window.$wireui.notify({
                title: '{{ __('File upload failed') }}',
                description: '{{ __('Your file upload failed. Please try again.') }}',
                icon: 'error'
            });
        },
        uploadSuccess(success, files) {
            this.isUploading = false;
            this.progress = 0;
        },
        uploadProgress(progress) {
            this.progress = progress;
        },
        uploadFiles(files, event) {
            this.isUploading = true;
            let $this = this;
            $wire.uploadMultiple('{{ $target }}', files,
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
    }">
        <div>
            {{ $label ?? '' }}
        </div>
        <div class="relative flex flex-col items-center justify-center"
             x-on:drop="isDropping = false"
             x-on:drop.prevent="handleFileDrop($event)"
             x-on:dragover.prevent="isDropping = true"
             x-on:dragleave.prevent="isDropping = false"
        >
            <div class="absolute top-0 bottom-0 left-0 right-0 z-30 flex items-center justify-center bg-blue-500 opacity-90"
                 x-show="isDropping"
                 x-cloak
            >
                <span class="text-3xl text-white">{{ __('Release to upload!') }}</span>
            </div>
            <label class="order-2 flex w-full cursor-pointer select-none flex-col items-center justify-center rounded-md border-dashed border-gray-300 bg-gray-50 p-10 shadow hover:bg-slate-50 dark:bg-gray-700"
                   x-bind:for="uploadObjectId"
            >
                <div class="pb-3">
                    <x-heroicons name="arrow-up-on-square" class="h-12 w-12" />
                </div>
                <p>{{ __('Click here to select files to upload') }}</p>
                <em class="italic text-slate-400">{{ __('(Or drag files to the page)') }}</em>
                <div class="mt-3 h-[2px] w-1/2 bg-gray-200" x-show="isUploading" x-cloak>
                    <div
                        class="h-[2px] bg-blue-500 transition-all"
                        style="transition: width 1s"
                        x-bind:style="`width: ${progress}%;`"
                    >
                    </div>
                </div>
            </label>
            <input {{ $attributes->whereDoesntStartWith('wire') }} type="file" x-bind:id="uploadObjectId" class="hidden" @if($multiple) multiple @endif x-on:change="handleFileSelect($event)"/>
        </div>
        <div class="space-y-3">
            <template x-for="(file, index) in $wire.{{ $wireModel }}.stagedFiles">
                <x-card class="!py-0 !px-0" x-show="! file.shouldDelete" x-cloak>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex w-0 flex-1 items-center gap-1.5">
                            <div class="flex-shrink-0 rounded-md object-contain">
                                <img x-bind:src="file.preview_url ? file.preview_url : '#'" class="object-cover h-16 w-16 rounded-md" alt="">
                            </div>
                            <span class="w-0 flex-1 truncate pl-1" x-text="file.name"></span>
                        </div>
                        <div x-cloak x-show="file.id">
                            <x-button primary icon="download" wire:click="download(file.id)" />
                        </div>
                        <div class="flex flex-shrink-0 px-4">
                            <x-button negative x-on:click="file.shouldDelete = true" :label="__('Delete')" />
                        </div>
                    </div>
                </x-card>
            </template>
        </div>
    </div>
</div>
