@props([
    "selectable" => false,
    "sortable" => false,
    "hideIcon" => false,
    "tree" => null,
    "nameAttribute" => "label",
    "childrenAttribute" => "children",
    "parentIdAttribute" => "parent_id",
    "withSearch" => false,
    "searchAttributes" => null,
    "checkbox" => null,
    "suffix" => null,
])
@php
    if (is_null($tree)) {
        throw new InvalidArgumentException('The "tree" parameter is required for this component.');
    }
@endphp

<div
    {{ $attributes->except(["wire:model", "x-model", "moved", "nameAttribute", "childrenAttribute", "x-sort:item"]) }}
    x-data="folder_tree(
                {{ $tree }},
                '{{ $tree }}',
                {{ $attributes->hasAny(["wire:model", "x-model"]) && $selectable ? '$wire.' . $attributes->whereStartsWith(["wire:model", "x-model"])->first() : $attributes->get("model", "[]") }},
                {{ $attributes->has("multiselect") ? "true" : "false" }},
                '{{ $nameAttribute }}',
                '{{ $childrenAttribute }}',
                '{{ $parentIdAttribute }}',
                {{ $attributes->get("selected", "null") }},
                {{ $attributes->get("checked-callback", "null") }},
                @js($searchAttributes),
            )"
>
    <!-- Root Rendering of the Tree -->
    {{ $header ?? null }}
    <div class="tree-container w-full gap-4 lg:flex">
        <ul class="tree grow pl-2">
            {{ $beforeTree ?? null }}
            @if ($withSearch)
                <div class="pb-2">
                    <x-input
                        type="search"
                        x-model.debounce.500ms="search"
                        placeholder="{{ __('Search') }}"
                    />
                </div>
            @endif

            <template
                @if($withSearch) x-for="node in searchNodes(tree, search)" @else x-for="node in tree" @endif
                :key="node.id"
            >
                <li>
                    <template
                        x-template-outlet="$refs.treeNodeTemplate.querySelector('template')"
                        x-data="{ node: node }"
                    ></template>
                </li>
            </template>
            {{ $afterTree ?? null }}
        </ul>
        {{ $slot }}
    </div>

    <!-- Recursive Template -->
    <div x-ref="treeNodeTemplate">
        <template>
            <!-- this is the root level elements only -->
            <li>
                <div
                    class="-ml-3 flex cursor-pointer select-none items-center space-x-2 rounded px-1.5 text-sm text-gray-700 dark:text-gray-50"
                    x-on:click="toggleSelect(node)"
                    x-bind:class="selected?.id === node.id ? 'bg-indigo-500 dark:bg-indigo-700 text-white' : ''"
                >
                    <i
                        class="ph ph-caret-right text-base transition-transform duration-200"
                        x-bind:class="
                            node[childrenAttribute]
                                ? isOpen(node)
                                    ? 'rotate-90'
                                    : 'rotate-0'
                                : 'invisible'
                        "
                        x-on:click.stop="node[childrenAttribute] ? toggleOpen(node, $event) : null"
                    ></i>
                    @if ($selectable)
                        <template x-if="node.isSelectable ?? true">
                            <div>
                                @if ($checkbox?->isNotEmpty())
                                    {{ $checkbox }}
                                @else
                                    <x-checkbox
                                        sm
                                        x-effect="$el.indeterminate = isIndeterminate(node)"
                                        x-on:change="toggleCheck(node, $event.target.checked)"
                                        x-bind:checked="isChecked(node)"
                                        x-bind:value="node.id"
                                        class="form-checkbox"
                                    />
                                @endif
                            </div>
                        </template>
                    @endif

                    @if (! $hideIcon)
                        @if ($nodeIcon ?? false)
                            {{ $nodeIcon }}
                        @else
                            <i
                                class="ph text-base"
                                x-bind:class="
                                    node[childrenAttribute]
                                        ? isOpen(node)
                                            ? 'ph-folder-open'
                                            : 'ph-folder'
                                        : 'ph-file'
                                "
                            ></i>
                        @endif
                    @endif

                    <div
                        class="whitespace-nowrap"
                        x-html="node[nameAttribute]"
                    ></div>
                    {{ $suffix }}
                </div>
                <template x-if="node[childrenAttribute]?.length">
                    <ul
                        @if ($sortable)
                            x-sort="(item, position) => {{{ $attributes->get("moved", "null") }}}"
                            x-sort:group="folder-tree"
                        @endif
                        x-collapse
                        x-show="isOpen(node) && node[childrenAttribute]?.length"
                        class="tree__children border-l border-gray-200 pl-4 dark:border-slate-500"
                    >
                        @if ($sortable)
                            <li></li>
                        @endif

                        <template
                            @if($withSearch) x-for="childNode in searchNodes(node[childrenAttribute] ?? [], search)" @else x-for="childNode in node[childrenAttribute] ?? []" @endif
                            :key="childNode.id"
                        >
                            <!-- these are the lower levels -->
                            <li
                                data-child-node
                                class="tree__node flex flex-col pl-1.5"
                                @if ($attributes->has("x-sort:item"))
                                    x-sort:item="{{ $attributes->get("x-sort:item") }}"
                                    x-bind:data-id="{{ $attributes->get("x-sort:item") }}"
                                @else
                                    x-sort:item="childNode.id"
                                    x-bind:data-id="childNode.id"
                                @endif
                            >
                                <template
                                    x-template-outlet="$refs.treeNodeTemplate.querySelector('template')"
                                    x-data="{ node: childNode, parent: node }"
                                ></template>
                            </li>
                        </template>
                    </ul>
                </template>
            </li>
        </template>
    </div>
    {{ $footer ?? null }}
</div>
