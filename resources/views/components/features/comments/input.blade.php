<div class="comment-input px-4 py-6 sm:px-6"
     x-data="{
        user: @js(auth()->user()),
        avatarUrl: @js(auth()->user()->getAvatarUrl()),
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
     wire:ignore
>
    <div class="flex space-x-3">
        <div>
            <template x-if="user?.user_code && ! avatarUrl">
                <x-avatar :label="'-'" x-text="user.user_code">
                </x-avatar>
            </template>
            <template x-if="avatarUrl">
                <x-avatar src="#" x-bind:src="avatarUrl" />
            </template>
            <template x-if="! avatarUrl && ! user?.user_code">
                <x-avatar></x-avatar>
            </template>
        </div>
        <div class="min-w-0 flex-1">
            <div>
                <div>
                    <div>
                        <div
                            contenteditable
                            x-tribute.multiple="[
                                {values: $wire.get('users'), trigger: '@'},
                                {values: $wire.get('roles'), trigger: '#'},
                            ]"
                            x-ref="textarea"
                            placeholder="{{ __('Write something…') }}"
                            class="content-editable-placeholder placeholder-secondary-400 dark:bg-secondary-800 dark:placeholder-secondary-500 border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input block min-h-[85px] w-full rounded-md border p-3 shadow-sm transition duration-100 ease-in-out focus:outline-none dark:text-gray-50 sm:text-sm"
                            rows="3"></div>
                    </div>
                </div>
                <div class="flex justify-between mt-3">
                    <div class="flex gap-1.5">
                        <x-button flat icon="paper-clip" :label="__('Add attachment')" x-on:click="$refs.fileUpload.click()"/>
                        <input x-ref="fileUpload" class="hidden" multiple type="file" x-on:change="files = Array.from($el.files)">
                        <template x-for="(file, i) in files">
                            <x-badge rounded >
                                <x-slot:label>
                                    <span x-text="file.name"></span>
                                </x-slot:label>
                                <x-slot:append class="relative flex items-center w-2 h-2">
                                    <button x-on:click="removeUpload(i)" type="button">
                                        <x-icon name="x" class="w-4 h-4" />
                                    </button>
                                </x-slot:append>
                            </x-badge>
                        </template>
                    </div>
                    <div class="flex items-center justify-end space-x-4">
                        <x-toggle x-ref="sticky" md :left-label="__('Sticky')" />
                        <x-button
                            x-on:click="saveComment($refs.textarea, $refs.fileUpload, $refs.sticky, true); files = [];"
                            primary
                            wire:loading.attr="disabled" :label="auth()->user()->getMorphClass() === \FluxErp\Models\User::class && $this->isPublic === true ? __('Save internal') : __('Save')"/>
                        @if(auth()->user()->getMorphClass() === \FluxErp\Models\User::class && $this->isPublic === true)
                            <x-button
                                x-on:click="$wire.call('saveComment', content, sticky, false); content = ''; $refs.textarea.innerHTML = ''"
                                primary
                                wire:loading.attr="disabled" :label="__('Answer to customer')"/>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
