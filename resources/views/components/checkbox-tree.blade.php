@props([
    'selectable' => false,
    'tree' => null,
    'nameAttribute' => 'label',
    'childrenAttribute' => 'children',
])
@php
    if (is_null($tree)) {
        throw new InvalidArgumentException('The "tree" parameter is required for this component.');
    }
@endphp
<div
    {{ $attributes->except(['wire:model', 'x-model']) }}
    x-data="folder_tree(
        {{ $tree }},
        '{{ $tree }}',
        {{ $attributes->hasAny(['wire:model', 'x-model']) && $selectable ? '$wire.' . $attributes->whereStartsWith(['wire:model', 'x-model'])->first() : '[]' }},
        {{ $attributes->has('multiselect') ? 'true' : 'false' }},
        '{{ $nameAttribute }}',
        '{{ $childrenAttribute }}',
        {{ $attributes->get('selected', 'null') }},
        {{ $attributes->get('checked', 'null') }}
    )">
    <!-- Root Rendering of the Tree -->
    {{ $header ?? null }}
    <div class="tree-container flex gap-4 w-full">
        <ul class="tree pl-2">
            {{ $beforeTree ?? null }}
            <template x-for="node in tree" :key="node.id">
                <li>
                    <template
                        x-template-outlet="$refs.treeNodeTemplate.querySelector('template')"
                        x-data="{ node: node }">
                    </template>
                </li>
            </template>
            {{ $afterTree ?? null }}
        </ul>
        {{ $slot }}
    </div>

    <!-- Recursive Template -->
    <div x-ref="treeNodeTemplate">
        <template>
            <div class="tree__node flex flex-col pl-1.5">
                <div
                    class="flex items-center cursor-pointer space-x-2 px-1.5 -ml-3 rounded text-sm text-gray-700 dark:text-gray-50 select-none"
                    x-on:click="toggleSelect(node)"
                    x-bind:class="selected?.id === node.id ? 'bg-primary-500 dark:bg-primary-700 text-white' : ''">
                    <i
                        class="ph ph-caret-right transition-transform duration-200 text-base"
                        x-bind:class="node[childrenAttribute] ? (isOpen(node) ? 'rotate-90' : 'rotate-0') : 'invisible'"
                        x-on:click.stop="node[childrenAttribute] ? toggleOpen(node, $event) : null">
                    </i>
                    @if($selectable)
                        <x-checkbox
                            xs
                            x-effect="$el.indeterminate = isIndeterminate(node)"
                            x-on:change="toggleCheck(node, $event.target.checked)"
                            x-bind:checked="isChecked(node)"
                            x-bind:value="node.id"
                            class="form-checkbox"
                        />
                    @endif
                    <i class="ph text-base" x-bind:class="node[childrenAttribute] ? (isOpen(node) ? 'ph-folder-open' : 'ph-folder') : 'ph-file'"></i>
                    <div class="whitespace-nowrap" x-html="node[nameAttribute]"></div>
                </div>
                <ul
                    x-show="isOpen(node) && node[childrenAttribute]?.length"
                    class="tree__children pl-4 border-l border-gray-200 dark:border-slate-500">
                    <template x-for="childNode in node[childrenAttribute] ?? []" :key="childNode.id">
                        <li>
                            <template
                                x-template-outlet="$refs.treeNodeTemplate.querySelector('template')"
                                x-data="{ node: childNode, parent: node }">
                            </template>
                        </li>
                    </template>
                </ul>
            </div>
        </template>
    </div>
    {{ $footer ?? null }}
</div>
