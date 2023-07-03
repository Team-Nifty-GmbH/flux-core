<div class="comment-input px-4 py-6 sm:px-6" x-data="{user: @js(auth()->user()), avatarUrl: @js(auth()->user()->getAvatarUrl())}" wire:ignore>
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
            <div
                x-data="{
                    content: '',
                    sticky: false,
                }"
            >
                <div>
                    <div>
                        <div
                            contenteditable
                            x-tribute.multiple="[
                                {values: $wire.get('users'), trigger: '@'},
                                {values: $wire.get('roles'), trigger: '#'},
                            ]"
                            x-ref="textarea"
                            x-on:blur="content = $event.target.innerHTML"
                            placeholder="{{ __('Write something…') }}"
                            class="placeholder-secondary-400 dark:bg-secondary-800 dark:placeholder-secondary-500 border-secondary-300 focus:ring-primary-500 focus:border-primary-500 dark:border-secondary-600 form-input block min-h-[85px] w-full rounded-md border p-3 shadow-sm transition duration-100 ease-in-out focus:outline-none dark:text-gray-50 sm:text-sm"
                            rows="3"></div>
                    </div>
                </div>
                <div class="flex justify-between mt-3">
                    <div>
                        <x-button flat icon="paper-clip" :label="__('Add attachment')" x-on:click="uploadFile()"/>
                        <input class="hidden" multiple type="file" wire:model="files">
                    </div>
                    <div class="flex items-center justify-end space-x-4">
                        <x-toggle md :left-label="__('Sticky')" x-model="sticky"/>
                        <x-button
                            x-on:click="$wire.call('saveComment', content, sticky); content = ''; $refs.textarea.innerHTML = ''"
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
