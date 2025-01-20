<li x-bind:class="comment.is_sticky && 'bg-positive-50 dark:bg-positive-900'" class="px-4 py-6 sm:px-6">
    <div class="flex w-full space-x-3" x-bind:style="'padding-left:' + comment.slug_position?.match(/\./g)?.length * 40 + 'px'">
        <div class="dark:border-secondary-500 inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-gray-500 text-base dark:bg-gray-600">
            <div class="shrink-0 inline-flex items-center justify-center overflow-hidden rounded-full border border-gray-200 dark:border-secondary-500">
                <img class="shrink-0 object-cover object-center rounded-full w-10 h-10 text-base" x-bind:src="comment.user?.avatar_url" />
            </div>
        </div>
        <div class="w-full">
            <div class="flex justify-between text-sm">
                <div class="flex gap-1.5">
                    <div x-text="comment.user?.name ?? '{{ __('Unknown') }}'" class="font-medium text-gray-500"></div>
                    @if($this->isPublic === true)
                        <x-badge flat x-bind:class="! comment.is_internal && 'hidden'" :label="__('Internal')">
                        </x-badge>
                    @endif
                </div>
                @if(auth()->check())
                    <x-dropdown>
                        @canAction(\FluxErp\Actions\Comment\UpdateComment::class)
                            <x-dropdown.item x-on:click="$wire.toggleSticky(comment.id); comment.is_sticky = ! comment.is_sticky">
                                <span x-text="comment.is_sticky ? '{{ __('Unsticky') }}' : '{{ __('Sticky') }}'"></span>
                            </x-dropdown.item>
                        @endCanAction
                        <x-dropdown.item :label="__('Delete')"
                             x-bind:disabled="! comment.is_current_user"
                             wire:click="delete(comment.id)"
                             wire:flux-confirm.icon.error="{{ __('wire:confirm.delete', ['model' => __('Comment')]) }}"
                        />
                    </x-dropdown>
                @endif
            </div>
            <div class="mt-1 text-sm dark:text-gray-50">
                <p class="prose prose-sm dark:text-gray-50" x-html="comment.comment"></p>
                <div class="flex gap-1">
                    <template x-for="file in comment.media">
                        <x-button xs icon="paper-clip" x-on:click="$wire.download(file.id)" rounded>
                            <x-slot:label>
                                <span x-text="file.name"></span>
                            </x-slot:label>
                        </x-button>
                    </template>
                </div>
            </div>
            <div class="mt-2 space-x-2 text-sm font-medium text-gray-700 dark:text-gray-50">
                <span x-text="window.formatters.relativeTime(new Date(comment.created_at).getTime())"></span>
                <span x-text="'(' + window.formatters.datetime(new Date(comment.created_at)) + ')'"></span>
                @canAction(\FluxErp\Actions\Comment\CreateComment::class)
                    <span class="">&middot;</span>
                    <button
                        type="button"
                        x-on:click.prevent="
                            $refs.comments.querySelectorAll('.comment-input').forEach(function (el) {
                                el.remove();
                            });
                            $el.parentNode.insertAdjacentHTML('beforeend', $refs.textarea.innerHTML);
                            commentId = comment.id
                        "
                    >
                        {{ __('Answer') }}
                    </button>
                @endCanAction
            </div>
        </div>
    </div>
</li>
