@php
    $target = $attributes->wire('model')->value;
@endphp

<div>
    <div
        x-data="{
            isDropping: false,
            isUploading: false,
            progress: 0,
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
            uploadError(message = null) {
                this.isUploading = false
                this.progress = 0
                $tsui
                    .interaction('dialog')
                    .error(
                        '{{ __('File upload failed') }}',
                        message ??
                            '{{ __('Your file upload failed. Please try again.') }}',
                    )
                    .send()
            },
            rejectFiles(rejected) {
                let name = $nuxbe.escapeHtml(rejected.file.name)

                if (rejected.reason === 'size') {
                    return this.uploadError(
                        '{{ __('The file :name is :size. The maximum upload size is :max.') }}'
                            .replace(':name', () => name)
                            .replace(':size', () => rejected.size)
                            .replace(':max', () => rejected.maxSize),
                    )
                }

                this.uploadError(
                    '{{ __('The file type of :name is not accepted. Allowed types: :types.') }}'
                        .replace(':name', () => name)
                        .replace(
                            ':types',
                            () => $nuxbe.escapeHtml(rejected.accept),
                        ),
                )
            },
            uploadSuccess(success, files) {
                this.isUploading = false
                this.progress = 0
                $dispatch('file-uploaded', files)
            },
            uploadProgress(progress) {
                this.progress = progress
            },
            uploadFiles(files, event) {
                let rejected = $nuxbe.validateFiles(files, {
                    maxSize: {{ \FluxErp\Helpers\Helper::getMaxUploadSizeInBytes() }},
                    accept: '{{ $attributes->get('accept') }}',
                })

                if (rejected) {
                    event.target.value = ''

                    return this.rejectFiles(rejected)
                }

                this.isUploading = true
                let $this = this
                $wire.uploadMultiple(
                    '{{ $target }}',
                    files,
                    function (success) {
                        let uploadedFiles = event.target.files?.length
                            ? event.target.files
                            : event.dataTransfer.files
                        $this.uploadSuccess(success, uploadedFiles)
                        $wire.dispatch('updateFilesArray')
                    },
                    function (error) {
                        $this.uploadError()
                    },
                    function (event) {
                        $this.uploadProgress(event)
                    },
                )
            },
        }"
    >
        <div
            class="relative flex flex-col items-center justify-center"
            x-on:drop="isDropping = false"
            x-on:drop.prevent="handleFileDrop($event)"
            x-on:dragover.prevent="isDropping = true"
            x-on:dragleave.prevent="isDropping = false"
        >
            <div
                class="absolute top-0 right-0 bottom-0 left-0 z-30 flex items-center justify-center bg-indigo-500 opacity-90"
                x-show="isDropping"
            >
                <span class="text-3xl text-white">
                    {{ __('Release to upload!') }}
                </span>
            </div>
            <label
                class="order-2 flex w-full cursor-pointer flex-col items-center justify-center rounded-md border-dashed border-gray-300 bg-gray-50 p-10 shadow select-none hover:bg-slate-50 dark:bg-gray-700"
                for="file-upload"
            >
                <div class="pb-3">
                    <x-icon name="arrow-up-on-square" class="h-12 w-12" />
                </div>
                <p>{{ __('Click here to select files to upload') }}</p>
                <em class="text-slate-400 italic">
                    {{ __('(Or drag files to the page)') }}
                </em>
                <div
                    class="mt-3 h-[2px] w-1/2 bg-gray-200"
                    x-show="isUploading"
                >
                    <div
                        class="h-[2px] bg-blue-500"
                        style="transition: width 1s"
                        x-bind:style="`width: ${progress}%;`"
                    ></div>
                </div>
            </label>
            <input
                type="file"
                id="file-upload"
                class="hidden"
                multiple
                x-on:change="handleFileSelect($event)"
            />
        </div>
        <div class="space-y-3">
            <template x-for="(file, index) in $wire.filesArray">
                <div class="flex items-center justify-between text-sm">
                    <div class="flex w-0 flex-1 items-center">
                        <x-icon name="paper-clip" class="h-4 w-4" />
                        <span
                            class="w-0 flex-1 truncate pl-1"
                            x-text="file.name"
                        ></span>
                    </div>
                    <div class="flex shrink-0 space-x-4">
                        <x-button
                            color="red"
                            x-on:click="$wire.removeFileUpload('{{ $target }}', index)"
                            :text="__('Delete')"
                        />
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
