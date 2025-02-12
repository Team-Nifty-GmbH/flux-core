<div class="flex w-full space-x-3">
    <div class="dark:border-secondary-500 inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-gray-500 text-base dark:bg-gray-600">
        <div class="shrink-0 inline-flex items-center justify-center overflow-hidden rounded-full border border-gray-200 dark:border-secondary-500">
            <img class="shrink-0 object-cover object-center rounded-full w-10 h-10 text-base" x-bind:src="node.user?.avatar_url" />
        </div>
    </div>
    <div class="w-full">
        <div class="flex justify-between text-sm">
            <div class="flex gap-1.5">
                <div x-text="node.created_by ?? '{{ __('Unknown') }}'" class="font-medium text-gray-500"></div>
                @if($this->isPublic === true)
                    <x-badge flat x-bind:class="! node.is_internal && 'hidden'" :label="__('Internal')">
                    </x-badge>
                @endif
            </div>
            @if(auth()->check())
                <x-dropdown>
                    @canAction(\FluxErp\Actions\Comment\UpdateComment::class)
                    <x-dropdown.item x-on:click="toggleSticky(node)">
                        <span x-text="node.is_sticky ? '{{ __('Unsticky') }}' : '{{ __('Sticky') }}'"></span>
                    </x-dropdown.item>
                    @endCanAction
                    <x-dropdown.item
                        :label="__('Delete')"
                        x-bind:disabled="! node.is_current_user"
                        wire:click="delete(node.id).then((success) => { if(success) removeNode(node)})"
                        wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Comment')]) }}"
                    />
                </x-dropdown>
            @endif
        </div>
        <div class="mt-1 text-sm dark:text-gray-50">
            <p class="prose prose-sm dark:text-gray-50" x-html="node.comment"></p>
            <div class="flex gap-1">
                <template x-for="file in node.media">
                    <div class="flex gap-0.5 outline-none inline-flex justify-center items-center group transition-all ease-in duration-150 focus:ring-2 focus:ring-offset-2 hover:shadow-sm disabled:opacity-80 disabled:cursor-not-allowed rounded-lg gap-x-2 text-sm px-4 py-2 border text-slate-500 hover:bg-slate-100 ring-slate-200
    dark:ring-slate-600 dark:border-slate-500 dark:hover:bg-slate-700
    dark:ring-offset-slate-800 dark:text-slate-400">
                        <img x-bind:src="file.preview_url === '' ? '{{ route('icons', ['name' => 'document', 'variant' => 'outline']) }}' : file.preview_url" class="w-6 h-6" x-bind:alt="file.name" />
                        <span x-text="file.name"></span>
                        <div class="flex">
                            <x-button
                                xs
                                class="h-full"
                                wire:click="download(file.id)"
                                icon="download"
                            />
                            <x-button
                                xs
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
        </div>
        <div class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-50">
            <span x-text="window.formatters.relativeTime(new Date(node.created_at).getTime())"></span>
            <span x-text="'(' + window.formatters.datetime(new Date(node.created_at)) + ')'"></span>
            @canAction(\FluxErp\Actions\Comment\CreateComment::class)
                <span class="">&middot;</span>
                <button
                    type="button"
                    x-on:click.prevent="
                        $refs.comments.querySelectorAll('.comment-input').forEach(function (el) {
                            el.remove();
                        });
                        $el.parentNode.insertAdjacentHTML('beforeend', $refs.textarea.innerHTML);
                        $wire.commentId = node.id
                    "
                >
                    {{ __('Answer') }}
                </button>
            @endCanAction
        </div>
    </div>
</div>
