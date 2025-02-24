<div
    class="comment-input"
    wire:ignore
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
        files: [],
        sticky: false,
        removeUpload(index) {
            this.files.splice(index, 1);
            this.updateInputValue(this.$refs.fileUpload);
        },
        updateInputValue(ref) {
            ref.value = '';
            const dataTransfer = new DataTransfer();
            this.files.forEach((file) => {
            const fileInput = new File([file], file.name);
                dataTransfer.items.add(fileInput);
            });
            ref.files = dataTransfer.files;
        }
    }"
>
    <div class="flex space-x-3">
        <div>
            <div class="shrink-0 inline-flex items-center justify-center overflow-hidden rounded-full border border-gray-200 dark:border-secondary-500">
                <img class="shrink-0 object-cover object-center rounded-full w-10 h-10 text-base" x-bind:src="avatarUrl" />
            </div>
        </div>
        <div class="min-w-0 flex-1">
            <div x-ref="upload">
                <div x-ref="textarea">
                    <x-flux::editor class="comment-input" />
                </div>
                <div class="grow pt-4">
                    @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                        <div class="flex flex-col items-end">
                            <div class="w-full mb-4">
                                <input x-init="loadFilePond(countChildren)" id="filepond-drop" type="file"/>
                            </div>
                        </div>
                    @endCanAction
                </div>
                <div class="flex flex-wrap justify-end">
                    <div class="flex items-center justify-end space-x-4">
                        <x-toggle x-ref="sticky" :label="__('Sticky')" position="left" />
                        <x-button color="secondary" light
                            x-on:click="saveComment($refs.textarea, tempFilesId, $refs.sticky, false, typeof comment !== 'undefined' ? comment : null).then((success) => {if(success) clearPond();})"
                            primary
                            spinner="saveComment"
                            wire:loading.attr="disabled"
                            x-bind:disabled="isLoadingFiles.length > 0"
                            :text="auth()->user()?->getMorphClass() === morph_alias(\FluxErp\Models\User::class) && $this->isPublic === true ? __('Save internal') : __('Save')"
                        />
                        @if(auth()->user()?->getMorphClass() === morph_alias(\FluxErp\Models\User::class) && $this->isPublic === true)
                            <x-button color="secondary" light
                                x-on:click="saveComment($refs.textarea, tempFilesId, $refs.sticky, false, typeof comment !== 'undefined' ? comment : null).then((success) => {if(success) clearPond();})"
                                primary
                                spinner="saveComment"
                                x-bind:disabled="isLoadingFiles.length > 0"
                                wire:loading.attr="disabled"
                                :text="__('Answer to customer')"
                            />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
