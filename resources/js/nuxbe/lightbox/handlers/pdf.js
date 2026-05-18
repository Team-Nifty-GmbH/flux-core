import { lightbox } from '../../lightbox.js';

lightbox.register({
    matches: ({ ext, mime }) => mime === 'application/pdf' || ext === 'pdf',
    render: ({ url, container }) => {
        const iframe = document.createElement('iframe');
        iframe.src = url;
        iframe.className = 'w-[90vw] h-[90vh] bg-white';
        container.appendChild(iframe);
    },
});
