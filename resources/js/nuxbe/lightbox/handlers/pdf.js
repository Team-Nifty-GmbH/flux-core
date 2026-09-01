import { lightbox } from '../../lightbox.js';

function buildSpinner() {
    const wrapper = document.createElement('div');
    wrapper.className = 'flex flex-col items-center gap-3 text-white';

    const ring = document.createElement('div');
    ring.className =
        'h-12 w-12 animate-spin rounded-full border-4 border-white/20 border-t-white';

    const label = document.createElement('span');
    label.className = 'text-sm';
    label.textContent = window.translations?.['Loading...'] ?? 'Loading...';

    wrapper.append(ring, label);

    return wrapper;
}

function buildError() {
    const wrapper = document.createElement('div');
    wrapper.className = 'flex flex-col items-center gap-3 text-white';

    const label = document.createElement('span');
    label.className = 'text-sm';
    label.textContent =
        window.translations?.['Could not load PDF'] ?? 'Could not load PDF';

    wrapper.append(label);

    return wrapper;
}

lightbox.register({
    matches: ({ ext, mime }) => mime === 'application/pdf' || ext === 'pdf',
    render: ({ url, container }) => {
        const spinner = buildSpinner();
        container.appendChild(spinner);

        const iframe = document.createElement('iframe');
        iframe.className = 'hidden w-[90vw] h-[90vh] bg-white';
        container.appendChild(iframe);

        let blobUrl = null;
        const controller = new AbortController();

        fetch(url, { credentials: 'same-origin', signal: controller.signal })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.blob();
            })
            .then((blob) => {
                blobUrl = URL.createObjectURL(
                    blob.type === 'application/pdf'
                        ? blob
                        : new Blob([blob], { type: 'application/pdf' }),
                );
                iframe.src = blobUrl;
                iframe.classList.remove('hidden');
                spinner.remove();
            })
            .catch((error) => {
                if (error.name === 'AbortError') {
                    return;
                }

                console.warn('[nuxbe lightbox] pdf fetch failed', error);
                spinner.replaceWith(buildError());
            });

        return () => {
            controller.abort();
            if (blobUrl) {
                URL.revokeObjectURL(blobUrl);
            }
        };
    },
});
