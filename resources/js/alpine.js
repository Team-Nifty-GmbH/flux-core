import Alpine from 'alpinejs'
import focus from '@alpinejs/focus'
import persist from '@alpinejs/persist'

Alpine.plugin(focus)
Alpine.plugin(persist)

if (typeof window.Livewire === 'undefined') {
    throw 'Livewire Sortable.js Plugin: window.Livewire is undefined. Make sure @livewireScripts is placed above this script include';
}

window.Livewire.directive('sortable', (el, directive, component) => {
    // Only fire this handler on the "root" directive.
    if (directive.modifiers.length > 0) {
        return;
    }

    let options = {};

    if (el.hasAttribute('wire:sortable.options')) {
        options = (new Function(`return ${el.getAttribute('wire:sortable.options')};`))();
    }

    el.livewire_sortable = window.Sortable.create(el, {
        ...options,
        multiDrag: true,
        draggable: '[wire\\:sortable\\.item]',
        handle: el.querySelector('[wire\\:sortable\\.handle]') ? '[wire\\:sortable\\.handle]' : null,
        sort: true,
        dataIdAttr: el.querySelector('[wire\\:sortable\\.item]') ? '[wire\\:sortable\\.item]' : 'data-id',
        animation: 200,
        delay: 100,
        fallbackOnBody: true,
        filter: '.sortable-filter',
        selectedClass: 'sortable-selected',
        ghostClass: "sortable-ghost",  // Class name for the drop placeholder
        chosenClass: "sortable-chosen",  // Class name for the chosen item
        dragClass: "sortable-drag",  // Class name for the dragging item
        group: {
            name: el.getAttribute('wire:sortable'),
            pull: false,
            put: false,
        },
        store: {
            set: function (sortable) {
                let items = sortable.toArray().map((value, index) => {
                    return {
                        order: index + 1,
                        value: value,
                    };
                });

                component.call(directive.method, items);
            },
        },
        onMove: function (evt) {
            // only allow to move on the same level
            if (evt.related.dataset.level !== evt.dragged.dataset.level) {
                return false;
            }

            // dont allow if the target is not sortable
            if (evt.related.className.indexOf('sortable-filter') !== -1) {
                return false;
            }
        },
    });
});

window.Livewire.directive('sortable-group', (el, directive, component) => {
    // Only fire this handler on the "root" group directive.
    if (! directive.modifiers.includes('item-group')) {
        return;
    }

    let options = {};

    if (el.hasAttribute('wire:sortable-group.options')) {
        options = (new Function(`return ${el.getAttribute('wire:sortable-group.options')};`))();
    }

    el.livewire_sortable = window.Sortable.create(el, {
        ...options,
        draggable: '[wire\\:sortable-group\\.item]',
        handle: el.querySelector('[wire\\:sortable-group\\.handle]') ? '[wire\\:sortable-group\\.handle]' : null,
        sort: true,
        animation: 200,
        dataIdAttr: 'data-id',
        selectedClass: 'selected',
        fallbackOnBody: true,
        filter: '.sortable-filter',
        ghostClass: "sortable-ghost",  // Class name for the drop placeholder
        chosenClass: "sortable-chosen",  // Class name for the chosen item
        dragClass: "sortable-drag",  // Class name for the dragging item
        easing: "cubic-bezier(1, 0, 0, 1)",
        group: {
            name: el.closest('[wire\\:sortable-group]').getAttribute('wire:sortable-group'),
            pull: true,
            put: true,
        },
        onMove: (evt) => {
            let masterEl = evt.to.closest('[wire\\:sortable-group]');
            masterEl.parentNode.querySelectorAll('.sortable-to').forEach((el) => {el.classList.remove('sortable-to')});
            if (evt.to !== masterEl) {
                evt.to.classList.add('sortable-to');
            }
        },
    });
});

Alpine.directive('currency', (el, { expression }, { evaluate }) => {
    const data = evaluate(expression);

    el.innerText = formatters.money(data.value, data.currency);
});

Alpine.directive('percentage', (el, { expression }, { evaluate }) => {
    el.innerText = formatters.percentage(evaluate(expression));
})

window.Alpine = Alpine;
Alpine.start();
