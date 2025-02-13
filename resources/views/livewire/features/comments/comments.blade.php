<div class="relative">
    <section>
        <div
            x-data="{
                ...comments(),
                user: @js(auth()->user()),
                avatarUrl: @js(auth()->user()?->getAvatarUrl())
            }"
        >
            <div>
                <template x-ref="textarea">
                    <x-flux::features.comments.input />
                </template>
                @if(resolve_static(\FluxErp\Actions\Comment\CreateComment::class, 'canPerformAction', [false]) || $this->isPublic === false)
                    <x-flux::features.comments.input />
                @endcan
                <div class="relative flex flex-col gap-12" x-ref="comments">
                    <x-spinner />
                    <div x-cloak x-show="stickyComments.length > 0">
                        <h3 class="pb-4">{{ __('Sticky comments') }}</h3>
                        <template x-for="comment in stickyComments" :key="comment.id">
                            <div class="bg-positive-50 dark:bg-positive-900 px-4 py-6 sm:px-6">
                                <x-flux::features.comments.comment x-model="comment" />
                            </div>
                        </template>
                    </div>
                    <div class="dark:divide-secondary-700 soft-scrollbar overflow-auto">
                        <h3 class="pb-4">{{ __('All comments') }}</h3>
                        <div class="tree-container gap-4 w-full">
                            <ul class="tree" role="list">
                                <template x-for="comment in comments" :key="comment.id">
                                    <li>
                                        <template
                                            x-template-outlet="$refs.treecommentTemplate.querySelector('template')"
                                            x-data="{ comment: comment }">
                                        </template>
                                    </li>
                                </template>
                                <li x-intersect="loadMore()"></li>
                            </ul>
                        </div>
                        <div x-ref="treecommentTemplate">
                            <template>
                                <ul>
                                    <li x-bind:class="comment.is_sticky && 'bg-positive-50 dark:bg-positive-900'" class="px-4 py-6 sm:px-6">
                                        <x-flux::features.comments.comment x-model="comment" />
                                    </li>
                                    <template x-if="comment.children?.length > 0">
                                        <ul class="pl-12">
                                            <template x-for="childcomment in comment.children" :key="childcomment.id">
                                                <li data-child-comment class="tree__comment" x-bind:data-id="childcomment.id">
                                                    <template
                                                        x-template-outlet="$refs.treecommentTemplate.querySelector('template')"
                                                        x-data="{ comment: childcomment, parent: comment }">
                                                    </template>
                                                </li>
                                            </template>
                                        </ul>
                                    </template>
                                </ul>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
