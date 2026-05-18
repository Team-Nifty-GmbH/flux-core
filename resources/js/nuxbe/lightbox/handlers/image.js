import { lightbox } from '../../lightbox.js';

const IMAGE_EXTS = new Set([
    'jpg',
    'jpeg',
    'png',
    'gif',
    'webp',
    'svg',
    'avif',
    'bmp',
]);

lightbox.register({
    matches: ({ ext, mime }) =>
        (mime && mime.startsWith('image/')) || IMAGE_EXTS.has(ext),
    render: ({ url, title, container }) => {
        const img = document.createElement('img');
        img.src = url;
        if (title) {
            img.alt = title;
        }
        img.className =
            'h-auto w-auto max-h-[90vh] max-w-[90vw] object-contain';
        img.style.minWidth = '16rem';
        img.style.minHeight = '16rem';
        container.appendChild(img);
    },
});
