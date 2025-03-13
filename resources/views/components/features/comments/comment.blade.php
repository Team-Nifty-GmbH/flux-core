<div class="flex w-full space-x-3">
    <div
        x-init="
            $nextTick(() => {
                $el.querySelector('img').src = comment.user?.avatar_url
            })
        "
    >
        <x-avatar image="{{ route('icons', ['name' => 'user']) }}" xl />
    </div>
    <div class="w-full">
        <div class="flex justify-between text-sm">
            <div class="flex gap-1.5">
                <div
                    x-text="comment.created_by ?? '{{ __("Unknown") }}'"
                    class="font-medium text-gray-500"
                ></div>
                @if ($this->isPublic === true)
                    <x-badge
                        flat
                        x-bind:class="! comment.is_internal && 'hidden'"
                        :text="__('Internal')"
                    ></x-badge>
                @endif
            </div>
            @if (auth()->check())
                <x-dropdown icon="ellipsis-vertical" static>
                    @canAction(\FluxErp\Actions\Comment\UpdateComment::class)
                    <x-dropdown.items
                        x-on:click="toggleSticky(comment); show = false;"
                    >
                        <span
                            x-text="comment.is_sticky ? '{{ __("Unsticky") }}' : '{{ __("Sticky") }}'"
                        ></span>
                    </x-dropdown.items>
                    @endCanAction
                    <x-dropdown.items
                        :text="__('Delete')"
                        x-bind:disabled="! comment.is_current_user"
                        wire:click="delete(comment.id).then((success) => { if(success) removeNode(comment)})"
                        wire:flux-confirm.type.error="{{ __('wire:confirm.delete', ['model' => __('Comment')]) }}"
                    />
                </x-dropdown>
            @endif
        </div>
        <div class="mt-1 text-sm dark:text-gray-50">
            <p
                class="prose prose-sm dark:prose-invert dark:text-gray-50"
                x-html="comment.comment"
            ></p>
            <div class="flex gap-1">
                <template x-for="file in comment.media">
                    <div
                        class="group flex inline-flex items-center justify-center gap-0.5 gap-x-2 rounded-lg border px-4 py-2 text-sm text-slate-500 outline-none ring-slate-200 transition-all duration-150 ease-in hover:bg-slate-100 hover:shadow-sm focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-80 dark:border-slate-500 dark:text-slate-400 dark:ring-slate-600 dark:ring-offset-slate-800 dark:hover:bg-slate-700"
                    >
                        <img
                            x-bind:src="
                                file.preview_url === ''
                                    ? '{{ route("icons", ["name" => "document", "variant" => "outline"]) }}'
                                    : file.preview_url
                            "
                            class="h-6 w-6"
                            x-bind:alt="file.name"
                        />
                        <span x-text="file.name"></span>
                        <div class="flex">
                            <x-button
                                color="secondary"
                                light
                                xs
                                class="h-full"
                                wire:click="download(file.id)"
                                icon="arrow-down-tray"
                            />
                            <x-button
                                color="secondary"
                                light
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
            <span
                x-text="window.formatters.relativeTime(new Date(comment.created_at).getTime())"
            ></span>
            <span
                x-text="'(' + window.formatters.datetime(new Date(comment.created_at)) + ')'"
            ></span>
            @canAction(\FluxErp\Actions\Comment\CreateComment::class)
            <span class="">&middot;</span>
            <button
                type="button"
                x-on:click.prevent="
                    $refs.comments.querySelectorAll('.comment-input').forEach(function (el) {
                        el.remove()
                    })
                    $el.parentNode.insertAdjacentHTML('beforeend', $refs.textarea.innerHTML)
                    $wire.commentId = comment.id
                "
            >
                {{ __("Answer") }}
            </button>
            @endCanAction
        </div>
    </div>
</div>
