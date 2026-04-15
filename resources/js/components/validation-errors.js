const ERROR_RING = [
    'ring-red-300',
    'focus-within:ring-red-500',
    'dark:ring-red-500',
    'dark:focus-within:ring-red-500',
];

const ERROR_TEXT = 'mt-1 block text-sm font-medium text-red-500';
const ERROR_ATTR = 'data-validation-error';

function toggleRing(wrapper, add) {
    if (!wrapper) return;

    ERROR_RING.forEach((cls) =>
        add ? wrapper.classList.add(cls) : wrapper.classList.remove(cls),
    );
}

function toggleError(container, property, message) {
    if (!container) return;

    let span = container.querySelector(`[${ERROR_ATTR}="${property}"]`);

    if (message) {
        if (!span) {
            span = document.createElement('span');
            span.setAttribute(ERROR_ATTR, property);
            span.className = ERROR_TEXT;
            container.appendChild(span);
        }

        span.textContent = message;
        span.style.display = '';
    } else if (span) {
        span.style.display = 'none';
        span.textContent = '';
    }
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
        if (
            attr.name === 'wire:model' ||
            attr.name.startsWith('wire:model.')
        ) {
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

function processComponent(component) {
    const el = component.el;

    if (!el) return;

    const errors = component.snapshot?.memo?.errors || {};
    const keys = Object.keys(errors);
    const matched = new Set();

    // Clear all previous error states first
    el.querySelectorAll(`[${ERROR_ATTR}]`).forEach((span) => {
        span.style.display = 'none';
        span.textContent = '';
    });

    el.querySelectorAll(
        '[wire\\:model], [wire\\:model\\.live], [wire\\:model\\.blur], [wire\\:model\\.defer]',
    ).forEach((input) => {
        toggleRing(findWrapper(input), false);
    });

    el.querySelectorAll('[x-data*="tallstackui_select"]').forEach((select) => {
        const button = select.querySelector('[dusk="tallstackui_select_open_close"]');
        toggleRing(findWrapper(button || select), false);
    });

    // Inputs with wire:model (x-input, x-number, x-textarea, x-select.native)
    el.querySelectorAll(
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

    // Styled selects (wire:model is consumed by Alpine, not in DOM)
    el.querySelectorAll('[x-data*="tallstackui_select"]').forEach((select) => {
        const prop = Alpine.$data(select)?.property;

        if (!prop) return;

        const hasError = keys.includes(prop) && errors[prop]?.length > 0;
        const button = select.querySelector(
            '[dusk="tallstackui_select_open_close"]',
        );

        toggleRing(findWrapper(button || select), hasError);
        toggleError(select, prop, hasError ? errors[prop][0] : null);

        if (hasError && isVisible(select)) {
            matched.add(prop);
        }
    });

    // Force Alpine to re-evaluate x-show/x-text for published error views
    el.querySelectorAll('[x-show*="$errors"], [x-text*="$errors"]').forEach(
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

    // Toast fallback for errors without a visible matching input
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
