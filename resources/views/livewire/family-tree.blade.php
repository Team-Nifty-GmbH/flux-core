<div
    class="overflow-x-auto p-4"
    x-data="familyTree(@js($tree))"
>
    <template x-if="tree">
        <div class="w-max">
            <div x-html="renderNode(tree)"></div>
        </div>
    </template>
</div>

@script
<script>
    Alpine.data('familyTree', (tree) => ({
        tree: tree,

        renderNode(node) {
            const hasChildren = node.children && node.children.length > 0;
            const childCount = hasChildren ? node.children.length : 0;

            const nodeClasses = node.is_current
                ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300'
                : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700';

            const baseClasses = 'whitespace-nowrap rounded-lg border px-4 py-2 text-sm font-medium shadow-sm transition-colors';
            const label = document.createTextNode(node.label).textContent;

            const nodeHtml = node.url
                ? `<a href="${node.url}" wire:navigate class="${baseClasses} ${nodeClasses}">${label}</a>`
                : `<div class="${baseClasses} ${nodeClasses}">${label}</div>`;

            let childrenHtml = '';
            if (hasChildren) {
                const lineClasses = 'bg-gray-300 dark:bg-gray-600';

                let childItems = node.children.map((child, index) => {
                    const isFirst = index === 0;
                    const isLast = index === childCount - 1;
                    const isOnly = childCount === 1;

                    let connector = '';
                    if (!isOnly) {
                        const leftHalf = `<div class="h-0.5 w-1/2 ${!isFirst ? lineClasses : ''}"></div>`;
                        const rightHalf = `<div class="h-0.5 w-1/2 ${!isLast ? lineClasses : ''}"></div>`;
                        connector = `<div class="flex w-full">${leftHalf}${rightHalf}</div>`;
                    }

                    return `<div class="flex flex-col items-center">${connector}<div class="h-6 w-0.5 ${lineClasses}"></div><div class="px-4">${this.renderNode(child)}</div></div>`;
                }).join('');

                childrenHtml = `<div class="h-6 w-0.5 ${lineClasses}"></div><div class="flex">${childItems}</div>`;
            }

            return `<div class="flex flex-col items-center">${nodeHtml}${childrenHtml}</div>`;
        },
    }));
</script>
@endscript
