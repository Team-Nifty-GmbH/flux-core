<li x-bind:class="comment.is_sticky && 'bg-positive-50 dark:bg-positive-900'" class="px-4 py-6 sm:px-6">
    <div class="flex w-full space-x-3" x-bind:style="'padding-left:' + comment.slug_position?.match(/\./g)?.length * 40 + 'px'">
        <div class="dark:border-secondary-500 inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-gray-200 bg-gray-500 text-base dark:bg-gray-600">
            <template x-if="comment.user?.user_code && ! comment.user?.avatar_url">
                <x-avatar>
                    <span x-text="comment.user.user_code" class="font-medium text-white dark:text-gray-200">
                    </span>
                </x-avatar>
            </template>
            <template x-if="comment.user?.avatar_url">
                <x-avatar src="#" x-bind:src="comment.user?.avatar_url" />
            </template>
            <template x-if="! comment.user?.avatar_url && ! comment.user?.user_code">
                <x-avatar />
            </template>
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
                <x-dropdown>
                    @can('api.comments.put')
                        <x-dropdown.item x-on:click="$wire.toggleSticky(comment.id); comment.is_sticky = ! comment.is_sticky">
                            <span x-text="comment.is_sticky ? '{{ __('Unsticky') }}' : '{{ __('Sticky') }}'"></span>
                        </x-dropdown.item>
                    @endcan
                    <x-dropdown.item :label="__('Delete')"
                         x-bind:disabled="! comment.is_current_user"
                         x-on:click="
                                  window.$wireui.confirmDialog({
                                  title: '{{ __('Delete comment') }}',
                                    description: '{{ __('Do you really want to delete this comment?') }}',
                                    icon: 'error',
                                    accept: {
                                        label: '{{ __('Delete') }}',
                                        method: 'delete',
                                        params: comment.id
                                    },
                                    reject: {
                                        label: '{{ __('Cancel') }}',
                                    }
                                    }, '{{ $this->id }}')
                        "
                    />
                </x-dropdown>
            </div>
            <div class="mt-1 text-sm dark:text-gray-50">
                <p x-html="comment.comment"></p>
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
                @can('api.comments.post')
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
                @endcan
            </div>
        </div>
    </div>
</li>
