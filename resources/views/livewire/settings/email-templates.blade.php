<div
    x-data="{
        addReceiver($event, type) {
            let value = $event.target.value
            if ($event instanceof KeyboardEvent && $event.which !== 13) {
                value = value.slice(0, -1)
            }

            value = value.trim()

            if (
                value &&
                ($event instanceof FocusEvent ||
                    $event.code === 'Comma' ||
                    $event.code === 'Enter' ||
                    $event.code === 'Space')
            ) {
                const email = value.match(/<([^>]*)>/)
                if (email && email[1]) {
                    value = email[1]
                }
                if ($wire.emailTemplateForm[type] === null) {
                    $wire.emailTemplateForm[type] = []
                }

                $wire.emailTemplateForm[type].push(value)
                $event.target.value = null
            }
        },
    }"
>
    <x-modal :id="$emailTemplateForm->modalName()">
        <div class="flex flex-col gap-4">
            <x-input wire:model="emailTemplateForm.name" :label="__('Name')" />
            <x-select.styled wire:model="emailTemplateForm.model_type" :label="__('Model Type')" :options="$modelTypes" />
            <div class="flex flex-col gap-1.5">
            <x-label :label="__('To')" />
            <div class="flex gap-1">
                <template x-for="to in $wire.emailTemplateForm.to || []">
                    <x-badge flat color="indigo" cl>
                        <x-slot:text>
                            <span x-text="to"></span>
                        </x-slot>
                        <x-slot
                            name="right"
                            class="relative flex h-2 w-2 items-center"
                        >
                            <button
                                type="button"
                                x-on:click="$wire.emailTemplateForm.to.splice($wire.emailTemplateForm.to.indexOf(to), 1)"
                            >
                                <x-icon name="x-mark" class="h-4 w-4" />
                            </button>
                        </x-slot>
                    </x-badge>
                </template>
            </div>
            <x-input
                :placeholder="__('Add a new to')"
                x-on:blur="addReceiver($event, 'to')"
                x-on:keyup="addReceiver($event, 'to')"
                class="w-full"
            />
        </div>
        <div class="flex flex-col gap-1.5">
            <x-label :label="__('CC')" />
            <div class="flex gap-1">
                <template x-for="cc in $wire.emailTemplateForm.cc || []">
                    <x-badge flat color="indigo" cl>
                        <x-slot:text>
                            <span x-text="cc"></span>
                        </x-slot>
                        <x-slot
                            name="right"
                            class="relative flex h-2 w-2 items-center"
                        >
                            <button
                                type="button"
                                x-on:click="$wire.emailTemplateForm.cc.splice($wire.emailTemplateForm.cc.indexOf(cc), 1)"
                            >
                                <x-icon name="x-mark" class="h-4 w-4" />
                            </button>
                        </x-slot>
                    </x-badge>
                </template>
            </div>
            <x-input
                :placeholder="__('Add a new cc')"
                x-on:blur="addReceiver($event, 'cc')"
                x-on:keyup="addReceiver($event, 'cc')"
                class="w-full"
            />
        </div>
        <div class="flex flex-col gap-1.5">
            <x-label :label="__('BCC')" />
            <div class="flex gap-1">
                <template x-for="bcc in $wire.emailTemplateForm.bcc || []">
                    <x-badge flat color="indigo" cl>
                        <x-slot:text>
                            <span x-text="bcc"></span>
                        </x-slot>
                        <x-slot
                            name="right"
                            class="relative flex h-2 w-2 items-center"
                        >
                            <button
                                type="button"
                                x-on:click="$wire.emailTemplateForm.bcc.splice($wire.emailTemplateForm.bcc.indexOf(to), 1)"
                            >
                                <x-icon name="x-mark" class="h-4 w-4" />
                            </button>
                        </x-slot>
                    </x-badge>
                </template>
            </div>
            <x-input
                :placeholder="__('Add a new bcc')"
                x-on:blur="addReceiver($event, 'bcc')"
                x-on:keyup="addReceiver($event, 'bcc')"
                class="w-full"
            />
        </div>
            <x-textarea
                wire:model="emailTemplateForm.subject"
                :label="__('Subject')"
                rows="3"
            />
            <div class="flex gap-1 flex-wrap">
                <template x-for="file in $wire.emailTemplateForm.media ?? []">
                    <div
                        x-cloak
                        x-show="! $wire.emailTemplateForm.deleteMedia.includes(file.id)"
                        class="group inline-flex items-center justify-center gap-0.5 gap-x-2 rounded-lg border px-4 py-2 text-sm text-slate-500 outline-none ring-slate-200 transition-all duration-150 ease-in hover:bg-slate-100 hover:shadow-sm focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-80 dark:border-slate-500 dark:text-slate-400 dark:ring-slate-600 dark:ring-offset-slate-800 dark:hover:bg-slate-700"
                    >
                        <div class="flex items-center gap-1">
                            @canAction(\FluxErp\Actions\Media\DeleteMedia::class)
                                <x-button
                                    color="red"
                                    sm
                                    class="h-full"
                                    x-on:click="$wire.emailTemplateForm.deleteMedia.push(file.id)"
                                    icon="x-mark"
                                />
                            @endcanAction
                            <img
                                x-bind:src="
                                    file.preview_url === ''
                                        ? '{{ route('icons', ['name' => 'document', 'variant' => 'outline']) }}'
                                        : file.preview_url
                                "
                                class="h-6 w-6"
                                x-bind:alt="file.name"
                            />
                        </div>
                        <span x-text="file.name"></span>
                        <div class="flex">
                            <x-button
                                color="secondary"
                                light
                                sm
                                class="h-full"
                                wire:click="download(file.id)"
                                icon="arrow-down-tray"
                            />
                            <x-button
                                color="secondary"
                                light
                                sm
                                x-cloak
                                x-show="file.preview_url !== ''"
                                class="h-full"
                                x-on:click="$openDetailModal(file.original_url)"
                                icon="eye"
                            />
                        </div>
                    </div>
                </template>
            </div>
            <div>
                <div
                    x-on:clear-pond.window="clearPond()"
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
                            },
                        ),
                        selectionProxy: {},
                        selection: {},
                        countChildren() {
                            return this.selectionProxy?.children?.length
                        },
                        files: [],
                        sticky: false,
                        removeUpload(index) {
                            this.files.splice(index, 1)
                            this.updateInputValue(this.$refs.fileUpload)
                        },
                        updateInputValue(ref) {
                            ref.value = ''
                            const dataTransfer = new DataTransfer()
                            this.files.forEach((file) => {
                                const fileInput = new File([file], file.name)
                                dataTransfer.items.add(fileInput)
                            })
                            ref.files = dataTransfer.files
                        },
                    }"
                >
                    <div x-ref="upload">
                        @canAction(\FluxErp\Actions\Media\UploadMedia::class)
                            <div class="flex flex-col items-end">
                                <div class="mb-4 w-full">
                                    <input
                                        x-init="loadFilePond(countChildren)"
                                        id="filepond-drop"
                                        type="file"
                                    />
                                </div>
                            </div>
                        @endcanAction
                    </div>
                </div>
        </div>
            <x-flux::editor
                wire:model="emailTemplateForm.html_body"
                :label="__('Html Body')"
            />
            <x-textarea
                wire:model="emailTemplateForm.text_body"
                :label="__('Text Body')"
                rows="10"
            />
        </div>
        <x-slot:footer>
            <x-button
                color="secondary"
                light
                flat
                :text="__('Cancel')"
                x-on:click="$modalClose('{{ $emailTemplateForm->modalName() }}')"
            />
            <x-button
                color="indigo"
                :text="__('Save')"
                wire:click="save().then((success) => { if(success) $modalClose('{{ $emailTemplateForm->modalName() }}')})"
            />
        </x-slot>
    </x-modal>
</div>
