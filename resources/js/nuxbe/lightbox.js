const handlers = [];
let fallbackHandler = null;

function extFromUrl(url) {
    try {
        const path = new URL(url, window.location.origin).pathname;
        const match = path.match(/\.([a-z0-9]+)$/i);
        return match ? match[1].toLowerCase() : '';
    } catch {
        return '';
    }
}

function findHandler(url, mime) {
    const ext = extFromUrl(url);
    for (const handler of handlers) {
        if (handler.matches({ url, ext, mime })) {
            return handler;
        }
    }
    return fallbackHandler;
}

export const lightbox = {
    register(handler) {
        handlers.push(handler);
    },
    prepend(handler) {
        handlers.unshift(handler);
    },
    unregister(handler) {
        const index = handlers.indexOf(handler);
        if (index !== -1) {
            handlers.splice(index, 1);
        }
    },
    setFallback(handler) {
        fallbackHandler = handler;
    },
    resolve(url, mime) {
        return findHandler(url, mime);
    },
    extFromUrl,
};

export function openLightbox(url, options = {}) {
    if (!url) {
        return;
    }

    window.dispatchEvent(
        new CustomEvent('nuxbe:lightbox:open', {
            detail: {
                url,
                mime: options.mime ?? null,
                title: options.title ?? null,
            },
        }),
    );
}
