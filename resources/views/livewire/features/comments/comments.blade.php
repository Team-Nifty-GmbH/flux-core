<div class="relative">
    <section>
        <div x-data="{
                comments: $wire.entangle('comments').defer,
                stickyComments: $wire.entangle('stickyComments').defer,
                commentId: $wire.entangle('commentId').defer,
                uploadFile() {
                    $el.parentNode.querySelector('input[type=\'file\']').click();
                },
            }"
        >
            <div class="dark:divide-secondary-700 divide-y divide-gray-200">
                <template x-ref="textarea">
                        <x-features.comments.input />
                </template>
                @if(user_can('api.comments.post') || $this->isPublic === false)
                    <x-features.comments.input />
                @endcan
                <div class="relative">
                    <x-spinner />
                    <template x-if="stickyComments.length > 0">
                        <div class="dark:divide-secondary-700">
                            <h3 class="px-4 py-6 text-lg font-medium leading-6 text-gray-900 dark:text-gray-50 sm:px-6">{{ __('Sticky comments') }}</h3>
                            <ul role="list">
                                <template x-for="comment in stickyComments" >
                                    <x-features.comments.comment />
                                </template>
                            </ul>
                            <h3 class="px-4 py-6 text-lg font-medium leading-6 text-gray-900 dark:text-gray-50 sm:px-6">{{ __('All comments') }}</h3>
                        </div>
                    </template>
                    <div class="dark:divide-secondary-700 soft-scrollbar overflow-auto" x-ref="comments">
                        <ul role="list">
                            <template x-for="comment in comments.data" >
                                <x-features.comments.comment />
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
