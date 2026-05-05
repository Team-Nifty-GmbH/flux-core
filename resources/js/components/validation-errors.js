// Forces Alpine to re-evaluate vendor x-show/x-text bindings on
// $wire.$errors inside teleported modal/slide-over subtrees, where
// commit-driven reactivity doesn't propagate. Also drives the red ring
// state and the toast fallback for errors with no visible input.
const ERROR_RING = [
    'ring-red-300',
    'focus-within:ring-red-500',
    'dark:ring-red-500',
    'dark:focus-within:ring-red-500',
];

function toggleRing(wrapper, add) {
    if (!wrapper) return;

    ERROR_RING.forEach((cls) =>
        add ? wrapper.classList.add(cls) : wrapper.classList.remove(cls),
    );
}

function findWrapper(input) {
    const parent = input.parentElement;

    return (
        parent?.closest('[class*="ring-"], [class*="focus-within:ring-"]') ||
        parent
    );
}

function getWireModel(el) {
    for (const attr of el.attributes) {
        if (attr.name === 'wire:model' || attr.name.startsWith('wire:model.')) {
            return attr.value;
        }
    }

    return null;
}

function isVisible(el) {
    return el && el.offsetParent !== null;
}

function formatTitle(field) {
    return field
        .split('.')
        .map((s) =>
            /^\d+$/.test(s)
                ? Number(s) + 1
                : s.charAt(0).toUpperCase() + s.slice(1).replace(/_/g, ' '),
        )
        .join(' → ');
}

function getScopeRoots(componentEl) {
    // Component element plus any teleported descendants (e.g. TallStackUI
    // modals that x-teleport their contents to <body>) whose origin lives
    // inside the component. Without the teleported roots we'd miss inputs,
    // selects, and published @error spans rendered through <x-modal>,
    // <x-slide-over>, etc.
    const roots = [componentEl];

    document
        .querySelectorAll('[data-teleport-target]')
        .forEach((teleported) => {
            const origin = teleported._x_teleportBack;

            if (origin && componentEl.contains(origin)) {
                roots.push(teleported);
            }
        });

    return roots;
}

function queryAllScoped(roots, selector) {
    const results = [];

    roots.forEach((root) => {
        results.push(...root.querySelectorAll(selector));
    });

    return results;
}

function processComponent(component) {
    const el = component.el;

    if (!el) return;

    const errors = component.snapshot?.memo?.errors || {};
    const keys = Object.keys(errors);
    const matched = new Set();

    const roots = getScopeRoots(el);

    queryAllScoped(
        roots,
        '[wire\\:model], [wire\\:model\\.live], [wire\\:model\\.blur], [wire\\:model\\.defer]',
    ).forEach((input) => {
        const prop = getWireModel(input);

        if (!prop) return;

        const hasError = keys.includes(prop) && errors[prop]?.length > 0;

        toggleRing(findWrapper(input), hasError);

        if (hasError && isVisible(input)) {
            matched.add(prop);
        }
    });

    queryAllScoped(roots, '[x-data*="tallstackui_select"]').forEach((select) => {
        const prop = Alpine.$data(select)?.property;

        if (!prop) return;

        const hasError = keys.includes(prop) && errors[prop]?.length > 0;
        const button = select.querySelector(
            '[dusk="tallstackui_select_open_close"]',
        );

        toggleRing(findWrapper(button || select), hasError);

        if (hasError && isVisible(select)) {
            matched.add(prop);
        }
    });

    // Force Alpine to re-evaluate x-show/x-text for vendor-rendered error
    // spans inside teleported subtrees, where reactive bindings on
    // $wire.$errors don't trigger automatically.
    queryAllScoped(roots, '[x-show*="$errors"], [x-text*="$errors"]').forEach(
        (node) => {
            try {
                const xshow = node.getAttribute('x-show');
                const xtext = node.getAttribute('x-text');

                if (xshow) {
                    const visible = Alpine.evaluate(node, xshow);
                    node.style.display = visible ? '' : 'none';

                    if (visible) {
                        const prop = xshow.match(/has\('([^']+)'\)/)?.[1];

                        if (prop) matched.add(prop);
                    }
                }

                if (xtext) {
                    node.textContent = Alpine.evaluate(node, xtext) || '';
                }
            } catch (e) {
                console.warn('[validation-errors]', e);
            }
        },
    );

    if (typeof $tsui !== 'undefined') {
        keys.forEach((key) => {
            if (!matched.has(key) && errors[key]?.length > 0) {
                $tsui
                    .interaction('toast')
                    .error(formatTitle(key), errors[key][0])
                    .send();
            }
        });
    }
}

export default function validationErrors() {
    Livewire.hook('commit', ({ component, respond }) => {
        respond(() => {
            queueMicrotask(() => processComponent(component));
        });
    });
}
