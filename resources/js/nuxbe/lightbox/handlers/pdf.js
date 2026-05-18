import { lightbox } from '../../lightbox.js';

lightbox.register({
    matches: ({ ext, mime }) => mime === 'application/pdf' || ext === 'pdf',
    render: ({ url, container }) => {
        const iframe = document.createElement('iframe');
        iframe.className = 'w-[90vw] h-[90vh] bg-white';
        container.appendChild(iframe);

        let blobUrl = null;
        const controller = new AbortController();

        fetch(url, { credentials: 'same-origin', signal: controller.signal })
            .then((response) => response.blob())
            .then((blob) => {
                blobUrl = URL.createObjectURL(
                    blob.type === 'application/pdf'
                        ? blob
                        : new Blob([blob], { type: 'application/pdf' }),
                );
                iframe.src = blobUrl;
            })
            .catch((error) => {
                if (error.name !== 'AbortError') {
                    console.warn('[nuxbe lightbox] pdf fetch failed', error);
                }
            });

        return () => {
            controller.abort();
            if (blobUrl) {
                URL.revokeObjectURL(blobUrl);
            }
        };
    },
});
