<div class="overflow-x-auto p-4" x-data="familyTree(@js($tree))">
    <template x-if="tree">
        <div class="w-max">
            <div x-html="renderNode(tree)"></div>
        </div>
    </template>
</div>
