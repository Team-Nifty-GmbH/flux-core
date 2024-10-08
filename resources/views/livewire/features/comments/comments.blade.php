<div class="relative">
    <section>
        <div x-data="{
                comments: $wire.entangle('comments'),
                stickyComments: $wire.entangle('stickyComments'),
                commentId: $wire.entangle('commentId'),
                uploadProgress: function(progress) {
                },
                uploadSuccess: function(files) {
                },
                uploadFinished: function() {
                    this.uploadCache.files.value = '';
                    this.saveComment(this.uploadCache.content, null, this.uploadCache.sticky, this.uploadCache.internal);
                },
                uploadCache: null,
                saveComment: function(content, files, sticky, internal) {
                    const editor = Alpine.$data(content.querySelector('[x-data]')).editor();

                    if (files?.files.length > 0) {
                        this.uploadCache = {
                            content: content,
                            files: files,
                            sticky: sticky,
                            internal: internal
                        };

                        $wire.uploadMultiple(
                            'files',
                            files.files,
                            this.uploadSuccess,
                            this.uploadError,
                            this.uploadProgress
                        );

                        $wire.on('upload:finished', () => {
                            this.uploadFinished();
                        });

                        return;
                    }

                    $wire.saveComment(editor.getHTML(), sticky.checked, internal);
                    this.uploadCache = null;
                    editor.commands.setContent('', false);

                    sticky.checked = false;
                    $refs.comments.querySelectorAll('.comment-input')
                        .forEach(function (el) {
                            el.remove();
                        });
                },
                uploadError: function() {
                    window.$wireui.notify({
                        title: '{{ __('File upload failed') }}',
                        description: '{{ __('Your file upload failed. Please try again.') }}',
                        icon: 'error'
                    });
                },
            }"
        >
            <div class="dark:divide-secondary-700 divide-y divide-gray-200">
                <template x-ref="textarea">
                    <x-flux::features.comments.input />
                </template>
                @if(resolve_static(\FluxErp\Actions\Comment\CreateComment::class, 'canPerformAction', [false]) || $this->isPublic === false)
                    <x-flux::features.comments.input />
                @endcan
                <div class="relative">
                    <x-spinner />
                    <template x-if="stickyComments.length > 0">
                        <div class="dark:divide-secondary-700">
                            <h3 class="px-4 py-6 text-lg font-medium leading-6 text-gray-900 dark:text-gray-50 sm:px-6">{{ __('Sticky comments') }}</h3>
                            <ul role="list">
                                <template x-for="comment in stickyComments" >
                                    <x-flux::features.comments.comment />
                                </template>
                            </ul>
                            <h3 class="px-4 py-6 text-lg font-medium leading-6 text-gray-900 dark:text-gray-50 sm:px-6">{{ __('All comments') }}</h3>
                        </div>
                    </template>
                    <div class="dark:divide-secondary-700 soft-scrollbar overflow-auto" x-ref="comments">
                        <ul role="list">
                            <template x-for="comment in comments.data" >
                                <x-flux::features.comments.comment />
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
