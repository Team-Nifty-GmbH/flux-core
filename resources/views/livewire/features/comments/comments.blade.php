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
                        <template x-for="node in stickyComments" :key="node.id">
                            <div class="bg-positive-50 dark:bg-positive-900 px-4 py-6 sm:px-6">
                                <x-flux::features.comments.comment x-model="node" />
                            </div>
                        </template>
                    </div>
                    <div class="dark:divide-secondary-700 soft-scrollbar overflow-auto">
                        <h3 class="pb-4">{{ __('All comments') }}</h3>
                        <div class="tree-container gap-4 w-full">
                            <ul class="tree" role="list">
                                <template x-for="node in comments" :key="node.id">
                                    <li>
                                        <template
                                            x-template-outlet="$refs.treeNodeTemplate.querySelector('template')"
                                            x-data="{ node: node }">
                                        </template>
                                    </li>
                                </template>
                                <li x-intersect="loadMore()"></li>
                            </ul>
                        </div>
                        <div x-ref="treeNodeTemplate">
                            <template>
                                <ul>
                                    <li x-bind:class="node.is_sticky && 'bg-positive-50 dark:bg-positive-900'" class="px-4 py-6 sm:px-6">
                                        <x-flux::features.comments.comment x-model="node" />
                                    </li>
                                    <template x-if="node.children?.length > 0">
                                        <ul class="pl-12">
                                            <template x-for="childNode in node.children" :key="childNode.id">
                                                <li data-child-node class="tree__node" x-bind:data-id="childNode.id">
                                                    <template
                                                        x-template-outlet="$refs.treeNodeTemplate.querySelector('template')"
                                                        x-data="{ node: childNode, parent: node }">
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
