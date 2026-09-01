// Re-runs Alpine's x-teleport for templates whose teleported clone has been
// removed during wire:navigate. Livewire's dom-morph keeps the source
// template (and its `_x_teleport` back-reference), but Alpine's release
// callback already removed the clone in the target. Without this restore the
// dropdown/dialog/tooltip stays trapped in the <template> tag.
export default function teleportRestore() {
    document.querySelectorAll('template[x-teleport]').forEach((template) => {
        if (!template._x_teleport || template._x_teleport.isConnected) {
            return;
        }

        const scopeRoot = template.closest('[x-data]');
        if (!scopeRoot) {
            return;
        }

        const targetSelector = template.getAttribute('x-teleport');
        const target =
            targetSelector === 'body'
                ? document.body
                : document.querySelector(targetSelector);

        if (!target || !template.content.firstElementChild) {
            return;
        }

        const clone = template.content.firstElementChild.cloneNode(true);
        target.appendChild(clone);

        template._x_teleport = clone;
        clone._x_teleportBack = template;

        window.Alpine.addScopeToNode(clone, {}, scopeRoot);
        window.Alpine.initTree(clone);
    });
}
